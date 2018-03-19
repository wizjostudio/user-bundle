<?php
namespace Wizjo\Bundle\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class WizjoUserExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('wizjo_user.manager_config', $this->getManagersConfig($config['managers']));

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function getManagersConfig(array $config): array
    {
        $managers = [];

        foreach ($config as $name => $options) {
            $managers[$options['manager']] = [
                'entity' => $options['entity'],
                'name' => $name,
            ];
        }

        return $managers;
    }
}
