<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Entity\Image;

return static function (ContainerConfigurator $container) {
    $container->parameters()
        ->set('vich.field', Image::VICH_FIELD)
        ->set('vich.uri_prefix', Image::PUBLIC_DIR)
        ->set('vich.upload_destination', '%kernel.project_dir%/public' . Image::PUBLIC_DIR);
};