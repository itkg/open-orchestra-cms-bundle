<?php

namespace PHPOrchestra\BackofficeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('php_orchestra_backoffice');

        $rootNode->children()
            ->arrayNode('front_languages')
                ->info('Add the language available for the front with the key')
                ->useAttributeAsKey('key')
                ->prototype('scalar')->end()
            ->end()
            ->arrayNode('blocks')
                ->info('Add the block activated for the project')
                ->prototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}
