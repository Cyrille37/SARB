#!/usr/bin/env php
<?php
require_once ('Config.php');
require_once ('Twitter/TwitterBot.php');

/**
 * SARB
 *
 * A Twitter search and retweet bot
 *
 * PHP version 5.4+
 *
 * @category Internet
 * @package SARB
 * @author Cyrille37 <cyrille37@gmail.com>
 * @license GPL v3
 * @link https://github.com/Cyrille37/SARB
 */
class SARB
{

    /**
     *
     * @var Twitter\TwitterBot
     */
    protected $tBot;

    /**
     *
     * @param string $secretsFilename
     *            The filename where to read OAUTH_CONSUMER_KEY and OAUTH_CONSUMER_SECRET
     */
    public function __construct($secretsFilename)
    {
        /*
         * $secrets = file($secretsFilename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); $oauthConsumerKey = $oauthConsumerSecret = null; $oauthToken = $oauthTokenSecret = null; foreach ($secrets as $line) { list ($key, $value) = @explode(':', $line); switch (trim($key)) { case 'OAUTH_CONSUMER_KEY': $oauthConsumerKey = trim($value); break; case 'OAUTH_CONSUMER_SECRET': $oauthConsumerSecret = trim($value); break; case 'OAUTH_ACCESS_TOKEN': $oauthToken = trim($value); break; case 'OAUTH_ACCESS_SECRET': $oauthTokenSecret = trim($value); break; } }
         */
        $authData = Config::readSecretFile($secretsFilename);
        $this->tBot = new Twitter\TwitterBot($authData['oauthConsumerKey'], $authData['oauthConsumerSecret']);

        $this->tBot->setAccessToken( $authData['userId'], $authData['oauthToken'], $authData['oauthTokenSecret']);
    }

    public function run($searchString)
    {
        $tweets = $this->tBot->getUserTimeline('2300465034');
        var_export($tweets);

        echo "\n", 'Tweets count = ', count($tweets),"\n";

       // $this->_searchTweets();
    }


}

$opts = getopt('c:s:');

if(!isset($opts['c']) || !file_exists($opts['c']) )
{
    die('Must have a configuration file (-cConfigFilename)'."\n");   
}

if(!isset($opts['s']) || strlen(trim($opts['s']))==0 )
{
    die('Must have a search string (-sSearchString)'."\n");
}

$sarb = new SARB( $opts['c']);
$sarb->run($opts['s']);
