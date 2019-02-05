<?php

declare(strict_types=1);

namespace Milosa\SocialMediaAggregatorTests\Youtube;

use GuzzleHttp\Client;
use Milosa\SocialMediaAggregatorBundle\Youtube\YoutubeClient;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;

class YoutubeClientTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage api_key is required
     */
    public function testItRequiresApiKey(): void
    {
        new YoutubeClient(['no_api_key' => 'nope']);
    }

    public function testYoutubeClient(): void
    {
        $guzzleClient = $this->prophesize(Client::class);
        $guzzleClient->get(
            Argument::exact('https://www.googleapis.com/youtube/v3/search'),
            Argument::exact(['query' => [
                'key' => 'test_key',
                'part' => 'snippet,id',
                'type' => 'video',
            ]]))
            ->shouldBeCalledTimes(1)
            ->willReturn($this->prophesize(ResponseInterface::class)->reveal());

        $youtubeClient = new TestableYoutubeClient([
            'api_key' => 'test_key',
        ]);
        $youtubeClient->setGuzzleClient($guzzleClient->reveal());

        $youtubeClient->get('');
    }
}

class TestableYoutubeClient extends YoutubeClient
{
    public function setGuzzleClient(Client $guzzleClient): void
    {
        $this->client = $guzzleClient;
    }
}
