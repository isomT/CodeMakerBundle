<?php

namespace SBC\CodeMakerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class CodeMakerExtension extends Extension
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

        /**
         * FIRST APPROACH: Separated config block, getting it as a parameter
         *
         *  $container->setParameter( 'sbc_update_last_id', $config[ 'update_last_id' ] );
         *  $container->setParameter( 'sbc_respect_pattern', $config[ 'respect_pattern' ] );
         */
        $container->setParameter( 'cm_form_template', $config[ 'cm_form_template' ] );

        /**
         * SECOND APPROACH: Separated config block, injecting the config into a service
         *
         */
        $definition1 = $container->getDefinition('isom.code.maker');
        $definition1->addMethodCall( 'setConfig', array( $config ) );
        $definition2 = $container->getDefinition('code.maker.subscriber');
        $definition2->addMethodCall('setConfig', array( $config ));
    }
}
