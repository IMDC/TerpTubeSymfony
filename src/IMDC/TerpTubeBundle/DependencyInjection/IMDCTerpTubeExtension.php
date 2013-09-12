<?php

namespace IMDC\TerpTubeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class IMDCTerpTubeExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        
        $container->setParameter('imdc_ffmpeg.binary', $config['transcoding']['ffmpeg_binary']);
        $container->setParameter('imdc_ffprobe.binary', $config['transcoding']['ffprobe_binary']);
        $container->setParameter('imdc_ffmpeg.timeout', $config['transcoding']['timeout']);
        
        $container->setParameter('imdc_ffmpeg.config', array('ffmpeg.binaries'=>$config['transcoding']['ffmpeg_binary'],  'ffprobe.binaries' => $config['transcoding']['ffprobe_binary'], 'timeout' => $config['transcoding']['timeout']));
    }
}
