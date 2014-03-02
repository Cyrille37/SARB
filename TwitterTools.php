#!/usr/bin/env php
<?php
require_once ('Config.php');
require_once ('Twitter/TwitterBot.php');

/**
 * TwitterTools
 *
 * Some Twitter stuff
 *
 * PHP version 5.4+
 *
 * Twitter docs:
 *
 * - Working with timelines (max_id, since_id) https://dev.twitter.com/docs/working-with-timelines
 *
 * - Using the Twitter Search API https://dev.twitter.com/docs/using-search
 * - GET search/tweets https://dev.twitter.com/docs/api/1.1/get/search/tweets
 *
 * - Application-only authentication https://dev.twitter.com/docs/auth/application-only-auth
 *
 * Rate limits:
 * - user : 180 requests/queries per 15 minutes
 * - app only : 450 queries/requests per 15 minutes
 *
 * @category Internet
 * @package TwitterTools
 * @author Cyrille37 <cyrille37@gmail.com>
 * @license GPL v3
 * @link https://github.com/Cyrille37/SARB
 */
class TwitterTools
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
        $secrets = file($secretsFilename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $oauthConsumerKey = $oauthConsumerSecret = null;
        $oauthToken = $oauthTokenSecret = null;
        foreach ($secrets as $line) {
            list ($key, $value) = @explode(':', $line);
            switch (trim($key)) {
                case 'OAUTH_CONSUMER_KEY':
                    $oauthConsumerKey = trim($value);
                    break;
                case 'OAUTH_CONSUMER_SECRET':
                    $oauthConsumerSecret = trim($value);
                    break;
                case 'OAUTH_ACCESS_TOKEN':
                    $oauthToken = trim($value);
                    break;
                case 'OAUTH_ACCESS_SECRET':
                    $oauthTokenSecret = trim($value);
                    break;
            }
        }
        
        $this->tBot = new Twitter\TwitterBot($oauthConsumerKey, $oauthConsumerSecret);
        $this->tBot->setAccessToken($oauthToken, $oauthTokenSecret);
        */

        $authData = Config::readSecretFile($secretsFilename);
        $this->tBot = new Twitter\TwitterBot( $authData['oauthConsumerKey'], $authData['oauthConsumerSecret'] );
        $this->tBot->setAccessToken( $authData['userId'], $authData['oauthToken'], $authData['oauthTokenSecret'] );
    }

    /**
     */
    public function verifyCredentials()
    {
        $user = $this->tBot->verifyCredentials();
        echo 'Account: ', var_export($user, true), "\n";
        echo 'User: ', $user->getScreenName(), ' / ', $user->getId(), ' / ', $user->getLang(), "\n";
    }

    /**
     * Interactive console way to obtain user authorization and access token.
     * These token should be put in 'secrets.txt'.
     */
    public function userAuthApplication()
    {
        $requestToken = $this->tBot->getRequestToken();
        // echo 'request token: ', $requestToken['token']['oauth_token'],"\n";
        // echo 'request token_secret: ', $requestToken['token']['oauth_token_secret'],"\n";
        // echo 'request redirect_url: ', $requestToken['url'],"\n";
        
        echo 'To authorize SARB to use your account,', "\n", 'please visit ', $requestToken['url'], "\n";
        $pincode = self::ask_stdin('Please, enter the PIN: ');
        
        $accessToken = $this->tBot->getAccessToken($requestToken['token']['oauth_token'], $requestToken['token']['oauth_token_secret'], $pincode);
        echo 'Store these Access Token to permit SARB to use "', $accessToken['screen_name'], '" account.', "\n";
        echo 'OAUTH_ACCESS_TOKEN: ', $accessToken['oauth_token'], "\n";
        echo 'OAUTH_ACCESS_SECRET: ', $accessToken['oauth_token_secret'], "\n";
    }

    static function ask_stdin($msg)
    {
        echo $msg;
        $fr = fopen("php://stdin", "r");
        $input = fgets($fr, 255);
        fclose($fr);
        return trim($input);
    }

    /**
     *
     * @param string $query            
     * @param int $count            
     */
    public function search($query, $count)
    {
        $statuses = $this->tBot->searchRecentsTweets($query, $count);
        
        echo 'search found statuses count = ', count($statuses), "\n";
        
        $statusesId = array();
        foreach ($statuses as $status) {
            $id = $status->getId();
            if (isset($statusesId[$id]))
                $statusesId[$id] ++;
            else
                $statusesId[$id] = 1;
        }
        echo 'search uniques statused Id count = ', count($statusesId), "\n";
    }

    public function searchTimeline($query, $count)
    {
        $statuses = $this->tBot->searchTimelineTweets($query, $count);
    }

    /**
     *
     * @param string $query            
     * @param int $count            
     */
    public function analyseStory($query, $count)
    {
        $statuses = $this->tBot->searchRecentsTweets($query, $count);
        
        echo 'search found statuses count = ', count($statuses), "\n";
        
        $retweets = 0;
        $retweetsmore = 0;
        $notretweets = 0;
        $orignals = array();
        
        foreach ($statuses as $status) {
            // echo var_export($status,true),"\n";
            // $id = $status->getId ();
            // echo $id, ' ', $status->isRetweet(),"\n" ;
            if ($status->isRetweet()) {
                $retweets ++;
                $rid = $status->getRetweetedStatus()->getId();
                if (isset($orignals[$rid])) {
                    $retweetsmore ++;
                    $orignals[$rid] += 1;
                } else
                    $orignals[$rid] = 1;
            } else
                $notretweets ++;
        }
        
        $orphans = 0;
        foreach ($statuses as $status) {
            if (! $status->isRetweet())
                if (! isset($orignals[$status->getId()]))
                    $orphans ++;
        }
        
        $orignalsNotFound = 0;
        foreach ($orignals as $rid => $v) {
            $found = false;
            foreach ($statuses as $status) {
                if ($status->getId() == $rid) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $orignalsNotFound ++;
                $status = $this->tBot->getTweet($rid);
                echo '> ', $rid, ' ', $status->getCreatedAt(), ' @', $status->getUser()->getScreenName(), ' ', $status->getText(), "\n";
            }
        }
        
        echo 'search retweets count = ', $retweets, "\n";
        echo 'search notretweets count = ', $notretweets, "\n";
        echo 'search retweets+notretweets count = ', ($retweets + $notretweets), "\n\n";
        
        echo 'search retweetsmore count = ', $retweetsmore, "\n";
        echo 'search orignals count = ', count($orignals), "\n";
        echo 'search orphans count = ', $orphans, "\n";
        echo 'search orphans+orignals count = ', ($orphans + count($orignals)), "\n";
        
        echo 'search orignalsNotFound count = ', $orignalsNotFound, "\n";
        
        echo '$orignals: ', var_export($orignals, true), "\n";
    }
}

// ==================================

$opts = getopt('c:');

if (! isset($opts['c']) || ! file_exists($opts['c'])) {
    die('Must have a configuration file (-cConfigFilename)' . "\n");
}

$tt = new TwitterTools( $opts['c']);

//$tt->userAuthApplication();
$tt->verifyCredentials ();

// $tt->verifyCredentials ();
// $tt->analyseStory('#PTCE', 1000);
// $tt->search('#OSM', 1000);
// $tt->search('#PTCE', 1000);
// $tt->userAuthApplication ();
//$tt->searchTimeline('#PTCE', 1000);


