<?php
namespace Twitter;

require_once (__DIR__ . '/SearchMetaData.php');
require_once (__DIR__ . '/Status.php');
require_once (__DIR__ . '/User.php');

require_once (__DIR__ . '/twitteroauth/twitteroauth.php');

/**
 * A Twitter bot to talk with Twitter's servers.
 */
class TwitterBot
{

    protected $oauthConsumerKey;

    protected $oauthConsumerSecret;

    protected $bearerToken;

    protected $oauthToken;

    protected $oauthTokenSecret;

    /**
     *
     * @param string $oauthConsumerKey            
     * @param string $oauthConsumerSecret            
     */
    public function __construct($oauthConsumerKey, $oauthConsumerSecret)
    {
        if ($oauthConsumerKey == null || $oauthConsumerSecret == null)
            throw new ErrorException('OAUTH_CONSUMER stuff must be valid');
        
        $this->oauthConsumerKey = $oauthConsumerKey;
        $this->oauthConsumerSecret = $oauthConsumerSecret;
    }

    public function setAccessToken($oauthToken, $oauthTokenSecret)
    {
        $this->oauthToken = $oauthToken;
        $this->oauthTokenSecret = $oauthTokenSecret;
    }

    /**
     *
     * @return \Twitter\User
     */
    public function verifyCredentials()
    {
        $connection = new \TwitterOAuth($this->oauthConsumerKey, $this->oauthConsumerSecret, $this->oauthToken, $this->oauthTokenSecret);
        
        // Returns an HTTP 200 OK response code and a representation of the requesting user if authentication was successful;
        // returns a 401 status code and an error message if not. Use this method to test if supplied user credentials are valid.
        $account = $connection->get('account/verify_credentials');
        $user = User::createFrom($account);
        return $user;
    }

    /**
     */
    public function getRequestToken()
    {
        $connection = new \TwitterOAuth($this->oauthConsumerKey, $this->oauthConsumerSecret);
        $token = $connection->getRequestToken('oob');
        
        $redirect_url = $connection->getAuthorizeURL($token, false);
        
        return array(
            'token' => $token,
            'url' => $redirect_url
        );
    }

    public function getAccessToken($oauthToken, $oauthTokenSecret, $oauthVerifier)
    {
        $connection = new \TwitterOAuth($this->oauthConsumerKey, $this->oauthConsumerSecret, $oauthToken, $oauthTokenSecret);
        $token_credentials = $connection->getAccessToken($oauthVerifier);
        
        return $token_credentials;
    }

    /**
     * Request on twitter within an application-only context.
     *
     * @param string $url            
     * @param string $method            
     * @param array $headers            
     * @param array $data            
     * @throws Exception
     * @return string The server response
     */
    protected function _requestAppContext($url, $method, Array $headers, Array $data)
    {
        
        // CURL defaults to setting this to Expect: 100-Continue
        // which Twitter rejects !
        $headers['Expect'] = '';
        
        if ($this->bearerToken != null)
            $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
        
        $httpheaders = array();
        foreach ($headers as $k => $v) {
            $httpheaders[] = trim($k . ': ' . $v);
        }
        
        $c = curl_init();
        curl_setopt_array($c, array(
            CURLOPT_USERAGENT => \Config::HTTP_USERAGENT,
            CURLOPT_CONNECTTIMEOUT => \Config::HTTP_CONNECTTIMEOUT,
            CURLOPT_TIMEOUT => \Config::HTTP_TIMEOUT,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => \Config::HTTP_SSL_VERIFYPEER,
            CURLOPT_FOLLOWLOCATION => \Config::HTTP_FOLLOWLOCATION,
            CURLOPT_PROXY => \Config::HTTP_PROXY,
            CURLOPT_ENCODING => \Config::HTTP_ENCODING,
            CURLOPT_HTTPHEADER => $httpheaders,
            CURLINFO_HEADER_OUT => true
        ));
        
        if ($method == 'POST') {
            curl_setopt($c, CURLOPT_POST, true);
            
            $ps = array();
            foreach ($data as $k => $v) {
                $ps[] = "{$k}={$v}";
            }
            curl_setopt($c, CURLOPT_POSTFIELDS, implode('&', $ps));
        } else 
            if ($method == 'GET') {
                $params = array();
                foreach ($data as $k => $v) {
                    $params[] = urlencode($k) . '=' . urlencode($v);
                }
                $qs = implode('&', $params);
                $url = strlen($qs) > 0 ? $url . '?' . $qs : $this->url;
            } else {
                throw new \Exception('Request failed! Unknow method=' . $method);
            }
        
        // echo 'url: ', $url, "\n";
        
        curl_setopt($c, CURLOPT_URL, $url);
        
        $response = curl_exec($c);
        $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($c);
        curl_close($c);
        
        if ($code != 200) {
            throw new \Exception('Request failed! code=' . $code . ', response= ' . $response);
            // echo 'CODE : '.$code ."\n";
            // echo 'INFO: '.var_export($info,true)."\n";
            // echo 'RESPONSE: '.var_export($response, true)."\n";
        }
        return $response;
    }

    /**
     * Allows a registered application to obtain an OAuth 2 Bearer Token,
     * which can be used to make API requests on an application's own behalf,
     * without a user context.
     * This is called Application-only authentication.
     *
     * https://dev.twitter.com/docs/auth/application-only-auth
     * https://dev.twitter.com/docs/api/1.1/post/oauth2/token
     */
    public function getBearerToken()
    {
        if ($this->bearerToken != null)
            return $this->bearerToken;
        
        $creds = base64_encode(urlencode($this->oauthConsumerKey) . ':' . urlencode($this->oauthConsumerSecret));
        
        $headers = array();
        $headers['Authorization'] = 'Basic ' . $creds;
        $headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
        
        $response = $this->_requestAppContext(\Config::OAUTH_URL_BEARER_TOKEN, 'POST', $headers, array(
            'grant_type' => 'client_credentials'
        ));
        
        // RESPONSE: '
        $response = json_decode($response);
        // echo 'token_type: ', $response->token_type , "\n";
        // echo 'access_token: ', $response->access_token , "\n";
        
        if ($response->token_type != 'bearer') {
            throw new \Exception('Auth failed! Uknow how to handle token type = ' . $response->token_type);
        }
        
        $this->bearerToken = $response->access_token;
        return $this->bearerToken;
    }

    /**
     * Seach for tweets which match the query.
     * Only recents tweets (< 7 days ?!).
     * This method use the API 1.1 with a Bearer Token.
     * Recurcive function.
     *
     * GET search/tweets https://dev.twitter.com/docs/api/1.1/get/search/tweets
     * Using the Twitter Search API https://dev.twitter.com/docs/using-search
     * 
     * @param string $query            
     * @param int $count            
     * @param array $statuses            
     * @return \Twitter\Status[]
     */
    public function searchRecentsTweets($query, $count = \Config::SEARCH_RESULTS_DEFAULT, Array &$statuses = array())
    {
        $this->getBearerToken();
        
        // if( $statuses == null )
        // $statuses =array();
        
        $params = array(
            'q' => $query,
            'include_entities' => false ,
            'result_type' => 'mixed'
        );
        
        $statusesCount = count($statuses);
        if ($statusesCount > 0) {
            $maxId = $statuses[$statusesCount - 1]->getId() - 1;
            $params['max_id'] = $maxId;
        }
        
        $tmpCount = $count - $statusesCount;
        if ($tmpCount <= \Config::SEARCH_RESULTS_MAX)
            $params['count'] = $tmpCount;
        else
            $params['count'] = \Config::SEARCH_RESULTS_MAX;
        
        $headers = array();
        $response = $this->_requestAppContext(\Config::TWITTER_URL_SEARCH, 'GET', $headers, $params);
        
        $response = json_decode($response, true);
        // response should containts 2 keys: statuses & search_metadata
        // echo 'RESPONSE: ' . var_export ( $response, true ) . "\n";
        
        $smd = SearchMetaData::createFromArray($response['search_metadata']);
        echo var_export($smd,true),"\n";
        $respStatusesCount = count($response['statuses']);
        echo 'respStatusesCount = ',$respStatusesCount,"\n";
        
        // echo 'response statuses count = ', count ( $response ['statuses'] ), "\n";
        
        // $statuses = array ();
        foreach ($response['statuses'] as $status) {
            $statuses[] = Status::createFrom($status);
        }
        // echo 'statuses count = ', count ( $statuses ), "\n";
        
        //if ($smd->asMoreResults())
        if( $respStatusesCount > 0 )
            $statuses = $this->searchTweets($query, $count, $statuses);

        return $statuses;
    }

    /**
     * FIXME : À faire pour retrouver tous les tweets sur plusieurs années
     * Cete méthode demande d'être identifiée avec un user (pas de Bearer Token).
     * 
     * https://twitter.com/i/search/timeline?q=%23PTCE&src=typd&include_available_features=1&include_entities=1&last_note_ts=0&scroll_cursor=TWEET-422868369579069441-428568485875023872
     * 
     * https://twitter.com/i/search/timeline
     * ?q=%23PTCE
     * &src=typd
     * &composed_count=0
     * &include_available_features=1
     * &include_entities=1
     * &include_new_items_bar=true
     * &interval=30000
     * &last_note_ts=0
     * &latent_count=0
     * &refresh_cursor=TWEET-422868369579069441-428568485875023872
     * 
     * https://twitter.com/i/search/timeline
     * ?q=%23PTCE
     * &src=typd
     * &include_available_features=1
     * &include_entities=1
     * &last_note_ts=0
     * &scroll_cursor=TWEET-356717097121878016-428568485875023872
     * 
     * https://twitter.com/i/search/timeline
     * ?q=%23PTCE
     * &src=typd
     * &include_available_features=1
     * &include_entities=1
     * &last_note_ts=0
     * &oldest_unread_id=0&scroll_cursor=TWEET-229842253957439488-428568485875023872
     * 
     * https://twitter.com/i/search/timeline
     * ?q=%23PTCE&src=typd
     * &include_available_features=1
     * &include_entities=1
     * &last_note_ts=0
     * &oldest_unread_id=0
     * &scroll_cursor=TWEET-229842253957439488-428568485875023872
     * 
     * @param unknown $query
     * @param unknown $count
     * @param array $statuses
     * @return \Twitter\Status
     */
    public function searchTimelineTweets($query, $count = \Config::SEARCH_RESULTS_DEFAULT, Array &$statuses = array())
    {
        
        //$this->getBearerToken();
        $connection = new \TwitterOAuth($this->oauthConsumerKey, $this->oauthConsumerSecret, $this->oauthToken, $this->oauthTokenSecret);
        
        // if( $statuses == null )
        // $statuses =array();

        $params = array(
            'q' => $query,
            'include_entities' => false ,
        );

        $statusesCount = count($statuses);

        $headers = array();
        //$response = $this->_requestAppContext(\Config::TWITTER_URL_TIMELINE, 'GET', $headers, $params);
        $response = $connection->get(\Config::TWITTER_URL_TIMELINE, $params );
        //$response = $connection->get('i/search/timeline', $params );
        //echo var_export($response,true),"\n";
        foreach( $response as $k=>$v )
        {
            echo $k, "\n";
        }
        $statuses = array();

        return $statuses;
    }
 
    /**
     * Returns a single Tweet, specified by the id parameter.
     * The Tweet's author will also be embedded within the tweet.
     *
     * https://dev.twitter.com/docs/api/1.1/get/statuses/show/%3Aid
     *
     * @param string $id            
     * @return \Twitter\Status
     */
    public function getTweet($id)
    {
        $this->getBearerToken();
        
        $params = array(
            'id' => $id
        );
        $headers = array();
        $response = $this->_requestAppContext(\Config::TWITTER_URL_SHOW, 'GET', $headers, $params);
        $response = json_decode($response, true);
        $status = Status::createFrom($response);
        return $status;
    }

}
