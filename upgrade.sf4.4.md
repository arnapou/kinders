Upgrade Symfony 4.4
===================

Liste du bootstrap du projet avant migration du code.

```
wget https://get.symfony.com/cli/installer -O - | bash
symfony new --full kinders --version=lts
cd kinders

composer update
composer require vich/uploader-bundle
composer require tetranz/select2entity-bundle
composer require stof/doctrine-extensions-bundle
composer require symfony/cache
composer require symfony/proxy-manager-bridge
composer require symfony/templating
composer require predis/predis
composer require snc/redis-bundle

```