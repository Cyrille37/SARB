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
 * examples :
 * $ ./SARB.php -csecrets.txt -s'#PTCE'
 * $ ./SARB.php -csecrets.txt -s'#PTCE' -lfr
 * $ ./SARB.php -csecrets.2291285905.txt -s'#PTCE' -lfr -z
 * 
 * @category Internet
 * @package SARB
 * @author Cyrille37 <cyrille37@gmail.com>
 * @license GPL v3
 * @link https://github.com/Cyrille37/SARB
 */
class SARB
{

    const SEARCH_COUNT = 150;

    const PAUSE_BETWEEN_RETWEET_MS = 250;

    /**
     *
     * @var Twitter\TwitterBot
     */
    protected $tBot;

    protected $simulation;

    /**
     *
     * @param string $secretsFilename
     *            The filename where to read OAUTH_CONSUMER_KEY and OAUTH_CONSUMER_SECRET
     */
    public function __construct($secretsFilename, $simulation = false)
    {
        $this->simulation = $simulation;
        
        $authData = Config::readSecretFile($secretsFilename);
        $this->tBot = new Twitter\TwitterBot($authData['oauthConsumerKey'], $authData['oauthConsumerSecret']);
        
        $this->tBot->setAccessToken($authData['userId'], $authData['oauthToken'], $authData['oauthTokenSecret']);
    }

    /**
     * Getting user timeline and blocks Ids then search tweets.
     * Fill the SARB account with no duplicate and no retweeted found tweets.
     * 
     * @param unknown $searchString
     * @param unknown $onlyLang
     * @throws Exception
     */
    public function run($searchString, $onlyLang)
    {
		if ($this->simulation) {
        	echo 'SIMULATION MODE','(searchString=', $searchString, '), (onlyLang=',$onlyLang,')',"\n";
        }

        try {
        	$userTweets = $this->tBot->getUserTimeline();
        	if( ! is_array($userTweets))
        		throw new \Exception('Failed to getUserTimeline()');

        	if ($this->simulation) {
        		echo 'User tweets count = ', count($userTweets), "\n";
        	}

        	$blockedUsersIds = $this->tBot->getBlocksIds();
        	if( ! is_array($blockedUsersIds))
        		throw new \Exception('Failed to getBlocksIds()');

        	if ($this->simulation) {
        		echo 'Blocked users count = ', count($blockedUsersIds), "\n";
        	}

        	$foundTweets = $this->tBot->searchTweets($searchString, self::SEARCH_COUNT, $onlyLang);
        	if( ! is_array($foundTweets))
        		throw new \Exception('Failed to searchTweets()');

        	if ($this->simulation) {
        		echo 'Found tweets count = ', count($foundTweets), "\n";
        	}

        }catch( Exception $ex ){

        	error_log('ERROR: '.$ex->getMessage());
        	error_log('ERROR: At line '.$ex->getLine().' in '.$ex->getFile()."\n");
        	error_log($ex->getTraceAsString());
        	return ;
        }

        $toRetweets = array();
        foreach ($foundTweets as $ft)
        {
        	if ($this->simulation){
            	echo 'UserId: ', $ft->getUser()->getId(), "\n";
            	echo 'TweetId: ', $ft->getId(), ', Lang: ',$ft->getLang(), "\n";
        	}

        	// Do not process blocked user
        	if( in_array($ft->getUser()->getId(), $blockedUsersIds ) )
        	{
        		if ($this->simulation)
        			echo "\t", 'skip blocked user ', $ft->getUser()->getId(),"\n";
        		continue;
        	}

			// Do not process retweet
            if ($ft->isRetweet()) {
        		if ($this->simulation)
        			echo "\t", 'skip retweet',"\n";
            	continue;
            }

            $toRetweet = true;
            foreach ($userTweets as $ut)
            {
				// Do not retweet user's tweet
                if( $ft->getId() == $ut->getId())
                {
	        		if ($this->simulation)
	        			echo "\t", 'skip my tweet',"\n";
                	$toRetweet = false;
                    break;
                }
                // Already retweeted
                if( $ut->isRetweet() && $ft->getId() == $ut->getRetweetedStatus()->getId())
                {
					if ($this->simulation)
	        			echo "\t", 'skip already retweeted',"\n";
                	$toRetweet = false;
                    break;
                }
            }
            if ($toRetweet)
            {
                $toRetweets[] = $ft;
            }
		}

		if ($this->simulation)
		{
			echo "\n", 'To retweets count = ', count($toRetweets), "\n";
		}

		if ($this->simulation)
			return;

		foreach ($toRetweets as $tweet)
		{
			$this->tBot->retweet($tweet->getId());
			usleep(self::PAUSE_BETWEEN_RETWEET_MS);
		}
    }
}

// =======================================

$usage = array(
    array(
        'opt' => 'c:',
        'Configuration filename'
    ),
    array(
        'opt' => 's:',
        'Search string'
    ),
    array(
        'opt' => 'l::',
        'Select only tweet with this langage'
    ),
    array(
        'opt' => 'z:',
        'Simulation mode, to not retweet and output some information'
    )
);
$opts = getopt('c:s:l::z');

if (! isset($opts['c']) || ! file_exists($opts['c'])) {
    die('Must have a configuration file (-cConfigFilename)' . "\n");
}
if (! isset($opts['s']) || strlen(trim($opts['s'])) == 0) {
    die('Must have a search string (-sSearchString)' . "\n");
}
$onlyLang = null;
if (isset($opts['l'])) {
    $onlyLang = $opts['l'];
}
$simulation = isset($opts['z']) ? true : false;

$sarb = new SARB($opts['c'], $simulation);

$sarb->run($opts['s'], $onlyLang);


