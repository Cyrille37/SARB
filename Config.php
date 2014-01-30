<?php

class Config
{

    const OAUTH_URL_REQUEST_TOKEN = 'https://api.twitter.com/oauth/request_token';

    const OAUTH_URL_AUTHORIZE = 'https://api.twitter.com/oauth/authorize';

    const OAUTH_URL_ACCESS_TOKEN = 'https://api.twitter.com/oauth/access_token';

    const OAUTH_URL_BEARER_TOKEN = 'https://api.twitter.com//oauth2/token';

    const OAUTH_SIGNATURE_METHOD = 'HMAC-SHA1';

    const TWITTER_URL_SHOW = 'https://api.twitter.com/1.1/statuses/show.json' ;

    /**
     * https://dev.twitter.com/docs/api/1.1/get/search/tweets
     * @var string url for search
     */
    const TWITTER_URL_SEARCH = 'https://api.twitter.com/1.1/search/tweets.json';
 
    const TWITTER_URL_TIMELINE = 'https://twitter.com/i/search/timeline' ;
 
    /**
     * https://api.twitter.com/1.1/search/tweets.json
     * 
     * @var number
     */
    const SEARCH_RESULTS_DEFAULT = 15;

    /**
     * https://api.twitter.com/1.1/search/tweets.json
     * 
     * @var number
     */
    const SEARCH_RESULTS_MAX = 100;

    const HTTP_USERAGENT = 'SARB v0.1';

    const HTTP_CONNECTTIMEOUT = 5;

    const HTTP_TIMEOUT = 5;

    const HTTP_SSL_VERIFYPEER = true;

    const HTTP_FOLLOWLOCATION = false;

    const HTTP_PROXY = null;

    const HTTP_ENCODING = 'UTF-8';
}
