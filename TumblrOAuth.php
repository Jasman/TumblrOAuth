<?php

/**
 * 
 * @author Jasman
 * @copyright Ihsana IT Solution 2014
 * 
 * Basic code: Abraham Williams (abraham@abrah.am) http://abrah.am
 * 
 **/
 

// Load OAuth lib. You can find it at http://oauth.net */
// require_once('OAuth.php');
// 
// REGISTER APP HERE: https://www.tumblr.com/oauth/apps


class TumblrOAuth
{

    public $host = "http://api.tumblr.com/v2/";
    public $http_code;
    public $url;
    public $timeout = 30;
    public $connecttimeout = 30;
    public $ssl_verifypeer = false;

    public $http_info;
    public $useragent = 'TumblrOAuth';
    function requestTokenURL()
    {
        return 'http://www.tumblr.com/oauth/request_token';
    }
    function authorizeURL()
    {
        return 'http://www.tumblr.com/oauth/authorize';
    }
    function accessTokenURL()
    {
        return 'http://www.tumblr.com/oauth/access_token';
    }
    function authenticateURL()
    {
        return 'https://www.tumblr.com/oauth/authorize';
    }
    function __construct($consumer_key, $consumer_secret, $oauth_token = null, $oauth_token_secret = null)
    {
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
        $this->consumer_key = $consumer_key;
        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
        if (!empty($oauth_token) && !empty($oauth_token_secret))
        {
            $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
        } else
        {
            $this->token = null;
        }
    }
    function getRequestToken($oauth_callback)
    {
        $parameters = array();
        $parameters['oauth_callback'] = $oauth_callback;
        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }
    function oAuthRequest($url, $method, $parameters)
    {
        if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0)
        {
            $url = "{$this->host}{$url}";
        }
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
        $request->sign_request($this->sha1_method, $this->consumer, $this->token);
        switch ($method)
        {
            case 'GET':
                return $this->http($request->to_url(), 'GET');
            default:
                return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
        }
    }
    function http($url, $method, $postfields = null)
    {
        $this->http_info = array();
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
        curl_setopt($ci, CURLOPT_HEADER, false);
        switch ($method)
        {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields))
                {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
            case 'DELETE':
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if (!empty($postfields))
                {
                    $url = "{$url}?{$postfields}";
                }
        }

        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci));
        $this->url = $url;

        //print_r(array($this, 'getHeader'));

        curl_close($ci);
        return $response;
    }
    function getHeader($ch, $header)
    {
        $i = strpos($header, ':');
        if (!empty($i))
        {
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
            $value = trim(substr($header, $i + 2));
            $this->http_header[$key] = $value;
        }
        return strlen($header);
    }
    function getAuthorizeURL($token, $sign_in_with_tumblr = true)
    {
        if (is_array($token))
        {
            $token = $token['oauth_token'];
        }
        if (empty($sign_in_with_tumblr))
        {
            return $this->authorizeURL() . "?oauth_token={$token}";
        } else
        {
            return $this->authenticateURL() . "?oauth_token={$token}";
        }
    }


    function getAccessToken($oauth_verifier)
    {
        $parameters = array();
        $parameters['oauth_verifier'] = $oauth_verifier;
        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
        $token = OAuthUtil::parse_parameters($request);
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
        return $token;
    }

    function post($url, $parameters = array())
    {
        $response = $this->oAuthRequest($url, 'POST', $parameters);
        return $response;
    }
}

?>
