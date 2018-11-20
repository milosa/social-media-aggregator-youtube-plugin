# Milosa Social Media Aggregator Youtube Plugin
[![Build Status](https://travis-ci.org/milosa/social-media-aggregator-youtube-plugin.svg?branch=master)](https://travis-ci.org/milosa/social-media-aggregator-youtube-plugin)
[![Coverage Status](https://coveralls.io/repos/github/milosa/social-media-aggregator-youtube-plugin/badge.svg?branch=master)](https://coveralls.io/github/milosa/social-media-aggregator-youtube-plugin?branch=master)

Youtube plugin for [Milosa Social Media Aggregator Bundle](https://github.com/milosa/social-media-aggregator-bundle)
  
## Installation

`composer require milosa/social-media-aggregator-youtube-plugin`

## Usage

Get Youtube API access here: https://developers.google.com/youtube/v3/getting-started
 
Add the following config to your configuration file:

### Sample config file
    milosa_social_media_aggregator:
        plugins:
            youtube:
                auth_data:
                    api_key: '%env(YOUTUBE_API_KEY)%'
                enable_cache: true
                cache_lifetime: 3600
                number_of_items: 20
                channel_id: UCLA_DiR1FfKNvjuUpBHmylQ
                template: youtube.twig