<?php

namespace Tomdkd\SymfonyBundleGeneratorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SymfonyBundleGeneratorExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $serviceFolder = __DIR__ . '/../Resources/config';
        $fileLocator   = new FileLocator($serviceFolder);
        $loader        = new YamlFileLoader($container, $fileLocator);

        $loader->load('controller.yml');
        $loader->load('command.yml');
    }
}