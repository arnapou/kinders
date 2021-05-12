<?php

declare(strict_types=1);

/*
 * This file is part of the Arnapou Kinders package.
 *
 * (c) Arnaud Buathier <arnaud@arnapou.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineMigrations;

use App\Entity\SiteConfig;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210512172553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la config de la home.';
    }

    public function up(Schema $schema): void
    {
        $content = <<<HTML
            <p>
                Je collectionne les kinders depuis 2004, j'ai énormément de doubles et d'objets à mettre sur ce site.<br/>
                Par conséquent, il s'étoffe de jour en jour : n'hésitez pas à y revenir régulièrement 😉
            </p>
            <p>
                Ce site est là pour faire des échanges également.<br/>
                Vous pouvez m'envoyer un email avec le lien en bas de page.
            </p>
            <p>
                Pour parcourir le site, vous pouvez cliquer sur un des liens à gauche ou les catégories qui suivent.<br/>
                Bonne visite ! 🙂
            </p>
            HTML;

        $this->addSql(
            "
            REPLACE INTO `site_config`
                (`id`, `created_at`, `updated_at`, `name`, `slug`, `comment`, `description`)
            VALUES 
                (:id, NOW(), NOW(), :name, '', '', :content)
            ",
            ['id' => SiteConfig::ID_HOME, 'name' => 'Home', 'content' => $content]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM `site_config` WHERE `id`=:id',
            ['id' => SiteConfig::ID_HOME]
        );
    }
}
