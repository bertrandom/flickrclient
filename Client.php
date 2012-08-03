<?php

    namespace Bert\Flickr;
    
    class Client {

    	const ACCESS_TOKEN_URL 		= 'http://www.flickr.com/services/oauth/access_token';
    	const AUTHORIZE_URL 		= 'http://www.flickr.com/services/oauth/authorize';
    	const REQUEST_TOKEN_URL		= 'http://www.flickr.com/services/oauth/request_token';
        const METHOD_URL            = 'http://api.flickr.com/services/rest/';

    	private $consumer_key;
    	private $consumer_secret;
    	private $callback_url;
        
        private $access_token = null;
    	
    	public function __construct($consumer_key, $consumer_secret, $callback_url) {

    		$this->consumer_key = $consumer_key;
    		$this->consumer_secret = $consumer_secret;
    		$this->callback_url = $callback_url;
    		$this->oauth = new \OAuth($this->consumer_key, $this->consumer_secret);

    	}
    	
    	public function getRequestToken() {

    		return $this->oauth->getRequestToken(self::REQUEST_TOKEN_URL, $this->callback_url);

    	}

    	public function getAuthorizeURL($token) {

    		return self::AUTHORIZE_URL . '?oauth_token=' . $token['oauth_token'] . '&perms=read';		

    	}

    	public function setToken($token) {

    		$this->oauth->setToken($token['oauth_token'], $token['oauth_token_secret']);

    	}

    	public function getAccessToken($verifier) {

    		return $this->oauth->getAccessToken(self::ACCESS_TOKEN_URL, null, $verifier);

    	}
    	
    	public function setAccessToken($access_token) {

    		$this->access_token = $access_token;

    	}

    	public function callMethod($method, $params = array()) {

    		try {

                $params['method'] = $method;

    			if (!isset($params['format'])) {
    				$params['format'] = 'php_serial';
    			}

    			$this->oauth->enableDebug();
    			$this->oauth->fetch(self::METHOD_URL, $params);
    		    $response = $this->oauth->getLastResponse();
    			$response_data = unserialize($response);

                if ($response_data === FALSE) {
                    
                    return array(
                        'stat' => 'fail',
                        'response' => $response,
                        'responseInfo' => $this->oauth->getLastResponseInfo(),
                    );
                    
                }

                return $response_data;

    		} catch (\OAuthException $e) {

                return array(
                    'stat' => 'fail',
                    'responseInfo' => $this->oauth->getLastResponseInfo(),
                    'exception' => $e,
                );

            }

    	}
        
    }