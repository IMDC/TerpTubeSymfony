<?php

namespace IMDC\TerpTubeBundle\DependencyInjection;

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
        $rootNode = $treeBuilder->root('imdc_terp_tube');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode->children()
            ->arrayNode('transcoding')->isRequired()->children()
            ->scalarNode('ffmpeg_binary')->isRequired()->end()
            ->scalarNode('ffprobe_binary')->isRequired()->end()
            ->scalarNode('timeout')->defaultValue(60)->end()->end()
            ->end()
            ->arrayNode('resource_file')->isRequired()->children()
            ->scalarNode('web_root_path')->isRequired()->end()
            ->scalarNode('upload_path')->isRequired()->end()->end()
            ->end()
            ->arrayNode('tests')
            ->treatNullLike(array(
                'files_path' => '',
                'logs_path' => ''
            ))
            ->children()
            ->scalarNode('files_path')->defaultNull()->end()
            ->scalarNode('logs_path')->defaultNull()->end()->end()
            ->end()->end();

        return $treeBuilder;
    }
}
