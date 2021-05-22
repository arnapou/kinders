<?php

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use JSMin\JSMin;
use SplFileInfo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AssetBuilderListener extends AbstractExtension implements CacheWarmerInterface
{
    private const PUBLIC_DIR = 'assets';

    private array  $compiledFiles;
    private array  $files;
    private string $env;
    private string $dirSource;
    private string $dirDestination;
    private string $configFile;
    private string $dirPublic;

    public function __construct(
        ParameterBagInterface $parameterBag,
        KernelInterface $kernel
    ) {
        $this->files = $parameterBag->get('assetbuilder');
        $this->env = $kernel->getEnvironment();
        $this->configFile = __DIR__ . '/../../config/assetbuilder.php';
        $this->dirPublic = __DIR__ . '/../../public';
        $this->dirSource = __DIR__ . '/../../assets';
        $this->dirDestination = $this->dirPublic . '/' . self::PUBLIC_DIR;

        if (is_file($this->configFile)) {
            $this->compiledFiles = include $this->configFile;
        } else {
            $this->compiledFiles = [];
        }
    }

    public function getFilters()
    {
        return [
            new TwigFilter('assetbuilder', [$this, 'getCompiledFilename']),
        ];
    }

    public function warmUp($cacheDir)
    {
        $this->build();
    }

    public function isOptional(): bool
    {
        return false;
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if ('prod' !== $this->env && $this->shouldTheAssetsBeBuilt()) {
            $this->build();
        }
    }

    /**
     * Tells whether we should build all the asset files.
     */
    private function shouldTheAssetsBeBuilt(): bool
    {
        foreach ($this->files as $name => $sources) {
            // the file does exists
            $compiled = $this->getCompiledFilename($name);
            if (!$compiled || !is_file("$this->dirPublic/$compiled")) {
                return true;
            }

            // timestamp too old -> we need to update the files
            $sources = $this->getFullSourcePath($sources);
            if (filemtime("$this->dirPublic/$compiled") < $this->getMostRecentTimestampOfFiles($sources)) {
                return true;
            }

            // the file list has changed
            $hashList = $this->getHashOfSourceFileList($sources);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            if (!str_ends_with($compiled, ".$hashList.$extension")) {
                return true;
            }
        }

        return false;
    }

    private function getHashOfSourceFileList(array $sources): string
    {
        return substr(hash('md5', serialize($sources)), 0, 4);
    }

    private function build(): void
    {
        $this->emptyBuildDirectory();

        $compiledFiles = [];
        foreach ($this->files as $name => $sources) {
            $dependencies = [];
            [$asset, $content] = $this->getAssetContent($name, $sources, $dependencies);

            $this->writeFile("$this->dirDestination/$asset", $content);
            foreach ($dependencies as $from => $to) {
                $this->writeFile($to, file_get_contents($from));
            }

            $compiledFiles[$name] = self::PUBLIC_DIR . "/$asset";
        }

        $this->writeConfigFile($compiledFiles);
        $this->compiledFiles = $compiledFiles;
    }

    /**
     * @return array{string, string}
     */
    private function getAssetContent(string $name, array $sources, array &$dependencies): array
    {
        $sources = $this->getFullSourcePath($sources);
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $content = $this->getCompiledContent($extension, $sources, $dependencies);

        $hash = substr(hash('md5', $content), 0, 8);
        $hashList = $this->getHashOfSourceFileList($sources);
        $asset = substr($name, 0, -\strlen($extension)) . "$hash.$hashList.$extension";

        return [$asset, $content];
    }

    private function getCompiledContent(string $extension, array $filenames, array &$dependencies): string
    {
        $compiled = '';

        foreach ($filenames as $filename) {
            $chunk = match ($extension) {
                'js' => $this->getCompiledContentJS($filename),
                'css' => $this->getCompiledContentCss($filename, $dependencies),
                default => ''
            };
            $compiled .= $chunk ? trim($chunk) . "\n" : '';
        }

        return $compiled;
    }

    private function getCompiledContentJS(string $filename): string
    {
        $content = file_get_contents($filename);
        if ('.min.js' !== substr($filename, -7)) {
            return JSMin::minify($content);
        }

        return $content;
    }

    private function getCompiledContentCss(string $filename, array &$dependencies): string
    {
        $content = file_get_contents($filename);

        if ('.min.css' !== substr($filename, -8)) {
            $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!s', '', $content);
            $content = str_replace(["\r", "\t"], '', $content);
            $content = preg_replace('!\s*\n\s*!', '', $content);
        }

        $this->detectAndReplaceCssDependencies($content, \dirname($filename), $dependencies);

        return $content;
    }

    private function detectAndReplaceCssDependencies(string &$content, string $baseDir, array &$dependencies): void
    {
        if (!preg_match_all('!url\((.+?)\)!s', $content, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            $cssurl = $match[0];
            $depFile = trim(trim($match[1], '"'), "'");

            if (str_starts_with($depFile, '/') || str_starts_with($depFile, 'data:') || str_starts_with($depFile, 'https:') || str_starts_with($depFile, 'http:')) {
                continue;
            }

            if (is_file($path = "$baseDir/$depFile")) {
                $hashedFilename = $this->getHashedDependencyFilename($path);
                $dependencies[$path] = "$this->dirDestination/files/$hashedFilename";
                $content = str_replace($cssurl, "url(files/$hashedFilename)", $content);
            }
        }
    }

    /**
     * Sert aussi pour l'extension twig avec le filtre "assetbuilder".
     */
    public function getCompiledFilename(string $name): string
    {
        return $this->compiledFiles[basename($name)] ?? '';
    }

    /**
     * Pour garantir l'unicité du fichier on ajoute un hash dans son nom.
     */
    private function getHashedDependencyFilename(string $path): string
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $hash = substr(hash_file('md5', $path), 0, 8);
        $name = basename($path, ".$ext");

        return "$name.$hash.$ext";
    }

    /**
     * Vide totalement le dossier des assets buildés.
     */
    private function emptyBuildDirectory(): void
    {
        if (!is_dir($this->dirDestination)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->dirDestination,
                \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO
            ), \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as /* @var $file SplFileInfo */ $file) {
            @unlink($file->getPathname());
        }
    }

    /**
     * Récupère le timestamp le plus récent de la liste de fichiers.
     */
    private function getMostRecentTimestampOfFiles(array $filenames): int
    {
        $time = 0;

        foreach ($filenames as $filename) {
            if (!is_file($filename)) {
                throw new \OutOfBoundsException("File not found $filename");
            }

            if (($mtime = filemtime($filename)) > $time) {
                $time = $mtime;
            }
        }

        return $time;
    }

    /**
     * Construit le chemin absolu des sources à partir de la liste de chemins relatifs de la config YAML.
     *
     * @return string[]
     */
    private function getFullSourcePath(array $sources): array
    {
        return array_map(fn ($file) => "$this->dirSource/$file", $sources);
    }

    /**
     * @param array<string, string> $compiledFiles
     */
    private function writeConfigFile(array $compiledFiles): void
    {
        $content = '<?php // ' . date('Y-m-d H:i:s') . "\n"
            . 'return ' . var_export($compiledFiles, true) . ";\n";

        file_put_contents($this->configFile, $content, LOCK_EX);
    }

    private function writeFile(string $filename, string $content): void
    {
        $this->mkdir(\dirname($filename));
        file_put_contents($filename, $content, LOCK_EX);
    }

    private function mkdir(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
    }
}
