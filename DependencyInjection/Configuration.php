<?php

namespace Symfony\Cmf\Bundle\CreateBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('cmf_create');

        $rootNode
            ->fixXmlConfig('plain_text_type', 'plain_text_types')
            ->fixXmlConfig('rdf_config_dir', 'rdf_config_dirs')
            ->children()
                ->scalarNode('rest_controller_class')->defaultFalse()->end()
                ->arrayNode('map')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('role')->defaultValue('ROLE_ADMIN')->end()
                ->scalarNode('stanbol_url')->defaultValue('http://dev.iks-project.eu:8081')->end()
                ->scalarNode('fixed_toolbar')->defaultTrue()->end()
                ->scalarNode('editor_base_path')->defaultValue('/bundles/cmfcreate/vendor/ckeditor/')->end()
                ->arrayNode('plain_text_types')
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('rdf_config_dirs')
                    ->useAttributeAsKey('dir')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('auto_mapping')->defaultTrue()->end()
                ->scalarNode('object_mapper_service_id')->defaultNull()->end()

                ->arrayNode('persistence')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('phpcr')
                            ->addDefaultsIfNotSet()
                            ->canBeEnabled()
                            ->children()
                                ->scalarNode('manager_name')->defaultNull()->end()
                                ->arrayNode('image')
                                    ->addDefaultsIfNotSet()
                                    ->canBeUnset()
                                    ->canBeEnabled()
                                    ->children()
                                        // if the CmfMediaBundle is present, it will prepend configuration
                                        // to enable this and set its model_class
                                        ->scalarNode('model_class')->cannotBeEmpty()->end()
                                        ->scalarNode('controller_class')->defaultValue('Symfony\Cmf\Bundle\CreateBundle\Controller\ImageController')->end()
                                        ->scalarNode('basepath')->defaultValue('/cms/media')->end()
                                    ->end()
                                ->end()
                                ->booleanNode('delete')->defaultValue(false)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
