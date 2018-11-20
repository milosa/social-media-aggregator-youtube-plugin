<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorBundle\Youtube;

use Milosa\SocialMediaAggregatorBundle\MilosaSocialMediaAggregatorPlugin;
use Milosa\SocialMediaAggregatorBundle\Youtube\DependencyInjection\YoutubePluginExtension;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use function realpath;

class YoutubePlugin extends Bundle implements MilosaSocialMediaAggregatorPlugin
{
    public function getPluginName(): string
    {
        return 'youtube';
    }

    public function getTwigPath(): string
    {
        return realpath(__DIR__.'/../Resources/views');
    }

    public function addConfiguration(ArrayNodeDefinition $pluginNode): void
    {
        $pluginNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('auth_data')
                ->addDefaultsIfNotSet()
                ->isRequired()
                ->children()
                    ->scalarNode('api_key')->defaultNull()->end()
                ->end()
            ->end()
            ->booleanNode('enable_cache')->defaultFalse()->end()
            ->integerNode('cache_lifetime')->info('Cache lifetime in seconds')->defaultValue(3600)->end()
            ->integerNode('number_of_items')->defaultValue(10)->end()
            ->scalarNode('channel_id')->defaultNull()->info('Channel id of youtube channel. Click on the name of a channel when viewing a youtube video. You\'ll find the channel-id in the URL.')->end()
            ->scalarNode('template')->defaultValue('youtube.twig')->end()
        ->end();
    }

    public function load(array $config, ContainerBuilder $container): void
    {
        $extension = new YoutubePluginExtension();
        $extension->load($config, $container);
        $this->setContainerParameters($config, $container);
        $this->configureCaching($config, $container);
        $this->registerHandler($container);
    }

    private function registerHandler(ContainerBuilder $container): void
    {
        $aggregatorDefinition = $container->getDefinition('milosa_social_media_aggregator.aggregator');
        $aggregatorDefinition->addMethodCall('addHandler', [new Reference('milosa_social_media_aggregator.handler.youtube')]);
    }

    public function setContainerParameters(array $config, ContainerBuilder $container): void
    {
        $container->setParameter('milosa_social_media_aggregator.youtube_channel_id', $config['plugins']['youtube']['channel_id']);
        $container->setParameter('milosa_social_media_aggregator.youtube_number_of_items', $config['plugins']['youtube']['number_of_items']);
        $container->setParameter('milosa_social_media_aggregator.youtube_api_key', $config['plugins']['youtube']['auth_data']['api_key']);
    }

    public function configureCaching(array $config, ContainerBuilder $container): void
    {
        if ($config['plugins']['youtube']['enable_cache'] === true) {
            $cacheDefinition = new Definition(FilesystemAdapter::class, [
                'milosa_social',
                $config['plugins']['youtube']['cache_lifetime'],
                '%kernel.cache_dir%',
            ]);

            $container->setDefinition('milosa_social_media_aggregator.youtube_cache', $cacheDefinition)->addTag('cache.pool');
            $fetcherDefinition = $container->getDefinition('milosa_social_media_aggregator.fetcher.youtube');
            $fetcherDefinition->addMethodCall('setCache', [new Reference('milosa_social_media_aggregator.youtube_cache')]);
        }
    }
}
