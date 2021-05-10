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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AssetBuilderListener extends AbstractExtension implements CacheWarmerInterface
{
    private array           $compiledFiles;
    private array           $config;
    private string          $env;
    private string          $srcDir;
    private string          $destDir;
    private string          $configFile;
    private string          $publicDir;

    public function __construct(ContainerInterface $container, KernelInterface $kernel)
    {
        $this->config = $container->getParameter('assetbuilder');
        $this->env = $kernel->getEnvironment();
        $this->publicDir = $kernel->getProjectDir() . '/public';
        $this->destDir = $kernel->getProjectDir() . '/public/' . $this->config['publicDir'];
        $this->srcDir = $kernel->getProjectDir() . '/' . $this->config['assetsDir'];
        $this->configFile = $kernel->getProjectDir() . '/config/assetbuilder.php';
        $this->compiledFiles = ASSETBUILDER;
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
        if ('prod' === $this->env) {
            return;
        }

        foreach ($this->config['files'] as $name => $sources) {
            $compiled = $this->getCompiledFile($name);
            if (!$compiled || !is_file("$this->publicDir/$compiled")) {
                $this->build();
                break;
            }
            $sources = array_map(fn ($file) => "$this->srcDir/$file", $sources);
            if (filemtime("$this->publicDir/$compiled") < $this->moreRecentTimestamp($sources)) {
                $this->build();
                break;
            }
            $hashList = $this->hashList($sources);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            if (substr($compiled, -\strlen($extension) - 1 - \strlen($hashList)) !== "$hashList.$extension") {
                $this->build();
                break;
            }
        }
    }

    public function getFilters()
    {
        return [
            new TwigFilter('assetbuilder', [$this, 'getCompiledFile']),
        ];
    }

    private function hashList(array $sources): string
    {
        return substr(hash('md5', serialize($sources)), 0, 4);
    }

    private function build(): void
    {
        $this->cleanup();
        $compiledFiles = [];
        foreach ($this->config['files'] as $name => $sources) {
            $sources = array_map(fn ($file) => "$this->srcDir/$file", $sources);
            $hashList = $this->hashList($sources);
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            $content = $this->compileContent($extension, $sources, $dependancies);
            $hash = substr(hash('md5', $content), 0, 8);
            $asset = substr($name, 0, -\strlen($extension)) . "$hash.$hashList.$extension";
            if (!is_dir(\dirname("$this->destDir/$asset"))) {
                mkdir(\dirname("$this->destDir/$asset"), 0755, true);
            }
            if (!is_dir("$this->destDir/files")) {
                mkdir("$this->destDir/files", 0755, true);
            }
            file_put_contents("$this->destDir/$asset", $content, LOCK_EX);
            foreach ($dependancies as $from => $to) {
                copy($from, $to);
            }
            $compiledFiles[$name] = $this->config['publicDir'] . "/$asset";
        }
        $configContent = "<?php \ndefine('ASSETBUILDER', " . var_export($compiledFiles, true) . ");\n";
        $configContent .= '// ' . date('Y-m-d H:i:s') . "\n";
        file_put_contents($this->configFile, $configContent, LOCK_EX);
        $this->compiledFiles = $compiledFiles;
    }

    private function compileContent(string $extension, array $filenames, ?array &$deps): string
    {
        $deps = [];
        $compiled = '';
        foreach ($filenames as $filename) {
            $content = file_get_contents($filename);
            if ('css' === $extension) {
                $this->compileCssSingleContent($filename, $content, $deps);
            } elseif ('js' === $extension) {
                $this->compileJsSingleContent($filename, $content, $deps);
            }
            $compiled .= trim($content) . "\n";
        }

        return $compiled;
    }

    private function compileJsSingleContent(string $filename, string &$content, array &$deps): void
    {
        if ('.min.js' !== substr($filename, -7)) {
            $content = JSMin::minify($content);
        }
    }

    private function compileCssSingleContent(string $filename, string &$content, array &$deps): void
    {
        if ('.min.css' !== substr($filename, -8)) {
            $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!s', '', $content);
            $content = str_replace(["\r", "\t"], '', $content);
            $content = preg_replace('!\s*\n\s*!', '', $content);
        }

        if (preg_match_all('!url\((.+?)\)!s', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $cssurl = $match[0];
                $file = trim(trim($match[1], '"'), "'");
                if (0 === strpos($file, '/') || 0 === strpos($file, 'data:') || 0 === strpos($file, 'https:') || 0 === strpos($file, 'http:')) {
                    continue;
                }
                if (is_file($path = \dirname($filename) . "/$file")) {
                    $ext = pathinfo($path, PATHINFO_EXTENSION);
                    $hash = substr(hash_file('md5', $path), 0, 8);
                    $name = basename($path, ".$ext");
                    $deps[$path] = "$this->destDir/files/$name.$hash.$ext";
                    $content = str_replace($cssurl, "url(files/$name.$hash.$ext)", $content);
                }
            }
        }
    }

    private function cleanup(): void
    {
        if (!is_dir($this->destDir)) {
            return;
        }
        $flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_FILEINFO;
        $folders = new \RecursiveDirectoryIterator($this->destDir, $flags);
        $flags = \RecursiveIteratorIterator::LEAVES_ONLY;
        $files = new \RecursiveIteratorIterator($folders, $flags);

        foreach ($files as /* @var $file SplFileInfo */ $file) {
            @unlink($file->getPathname());
        }
    }

    private function moreRecentTimestamp(array $filenames): int
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

    public function getCompiledFile(string $name): string
    {
        return $this->compiledFiles[basename($name)] ?? '';
    }
}
