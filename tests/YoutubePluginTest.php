<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorTests\Youtube;

use Milosa\SocialMediaAggregatorBundle\Youtube\YoutubePlugin;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class YoutubePluginTest extends TestCase
{
    /**
     * @var YoutubePlugin
     */
    private $plugin;

    public function setUp(): void
    {
        $this->plugin = new YoutubePlugin();
    }

    public function testGetPluginName(): void
    {
        $this->assertEquals('youtube', $this->plugin->getPluginName());
    }

    public function testGetTwigPath(): void
    {
        $this->assertEquals(DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views', mb_substr($this->plugin->getTwigPath(), -16));
    }

    public function testLoad(): void
    {
        $container = $this->createContainer(false);

        $this->assertTrue($container->hasParameter('milosa_social_media_aggregator.youtube_channel_id'));
        $this->assertTrue($container->hasParameter('milosa_social_media_aggregator.youtube_number_of_items'));
        $this->assertTrue($container->hasParameter('milosa_social_media_aggregator.youtube_api_key'));

        $this->assertTrue($container->hasDefinition('milosa_social_media_aggregator.handler.youtube'));

        $this->assertFalse($container->hasDefinition('milosa_social_media_aggregator.youtube_cache'));
    }

    public function testLoadWithCache(): void
    {
        $container = $this->createContainer(true);

        $this->assertTrue($container->hasDefinition('milosa_social_media_aggregator.youtube_cache'));
        $this->assertTrue($container->getDefinition('milosa_social_media_aggregator.fetcher.youtube')->hasMethodCall('setCache'));
    }

    public function testAddConfiguration(): void
    {
        $arrayNode = new ArrayNodeDefinition('node');
        $this->plugin->addConfiguration($arrayNode);
        $node = $arrayNode->getNode();

        $this->assertFalse($node->isRequired());
        $this->assertTrue($node->hasDefaultValue());
        $this->assertSame([
            'auth_data' => [
                'api_key' => null,
            ],
            'enable_cache' => false,
            'cache_lifetime' => 3600,
            'number_of_items' => 10,
            'channel_id' => null,
            'template' => 'youtube.twig',
        ],
            $node->getDefaultValue());
    }

    /**
     * @param bool $enableCache
     *
     * @return ContainerBuilder
     */
    protected function createContainer(bool $enableCache): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $this->createFakeAggregatorDefinition($container);
        $this->plugin->load($this->createFakeConfig($enableCache), $container);

        return $container;
    }

    private function createFakeConfig(bool $enableCache = false): array
    {
        return [
            'plugins' => [
                'youtube' => [
                    'auth_data' => [
                        'api_key' => 'fake_api_key',
                    ],
                    'enable_cache' => $enableCache,
                    'cache_lifetime' => 123,
                    'number_of_items' => 42,
                    'channel_id' => 'NASA',
                ],
            ],
        ];
    }

    private function createFakeAggregatorDefinition(ContainerBuilder $container): void
    {
        $definition = new Definition(DummyAggregator::class);
        $container->setDefinition('milosa_social_media_aggregator.aggregator', $definition);
    }
}

class DummyAggregator
{
    public function addHandler(Handler $handler)
    {
    }
}
