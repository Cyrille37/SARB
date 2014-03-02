<?php

class Config
{

    static function readSecretFile($secretsFilename)
    {
        $secrets = file($secretsFilename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        $auth = array(
            'oauthConsumerKey' => null,
            'oauthConsumerSecret' => null,
            'oauthToken' => null,
            'oauthTokenSecret' => null,
            'userId' => null ,
            'userScreenName' => null ,
        );
        
        foreach ($secrets as $line) {
            $line = trim($line);
            if ($line == '' || $line[0] == '#') {
                continue;
            }
            list ($key, $value) = @explode(':', $line);
            switch (trim($key)) {
                case 'OAUTH_CONSUMER_KEY':
                    $auth['oauthConsumerKey'] = trim($value);
                    break;
                case 'OAUTH_CONSUMER_SECRET':
                    $auth['oauthConsumerSecret'] = trim($value);
                    break;
                case 'OAUTH_ACCESS_TOKEN':
                    $auth['oauthToken'] = trim($value);
                    break;
                case 'OAUTH_ACCESS_SECRET':
                    $auth['oauthTokenSecret'] = trim($value);
                    break;
                case 'USERID':
                    $auth['userId'] = trim($value);
                    break;
            }
        }
        
        return $auth ;
    }

}
