<?php

namespace Mell\Bundle\SimpleDtoBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class SimpleDtoExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->addContainerParams($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    private function addContainerParams(ContainerBuilder $container, array $config)
    {
        foreach ($config as $property => $value) {
            $this->bindParam($container, $value, $this->getAlias() . '.' . $property);
        }
    }

    /**
     * Recursively bind params to container
     *
     * @param ContainerBuilder $container
     * @param mixed $value
     * @param string $prefix
     */
    private function bindParam(ContainerBuilder $container, $value, $prefix)
    {
        if (is_array($value)) {
            foreach ($value as $p => $v) {
                $this->bindParam($container, $v, $prefix . '.' . (is_array($v) ? current($v) : $v));
            }
        }
        $container->setParameter($prefix, $value);
    }
}
