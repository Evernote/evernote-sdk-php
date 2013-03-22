<?php

namespace Evernote;

require_once dirname(__DIR__)."/Thrift.php";
require_once dirname(__DIR__)."/transport/TTransport.php";
require_once dirname(__DIR__)."/transport/THttpClient.php";
require_once dirname(__DIR__)."/protocol/TProtocol.php";
require_once dirname(__DIR__)."/protocol/TBinaryProtocol.php";
require_once dirname(__DIR__)."/packages/UserStore/UserStore.php";
require_once dirname(__DIR__)."/packages/UserStore/UserStore_constants.php";
require_once dirname(__DIR__)."/packages/NoteStore/NoteStore.php";

class Client
{
    private $consumerKey;
    private $consumerSecret;
    private $sandbox;
    private $serviceHost;
    private $additionalHeaders;
    private $token;
    private $secret;

    public function __construct($options)
    {
        $this->consumerKey = isset($options['consumerKey']) ? $options['consumerKey'] : null;
        $this->consumerSecret = isset($options['consumerSecret']) ? $options['consumerSecret'] : null;

        $options += array('sandbox' => true);
        $this->sandbox = $options['sandbox'];

        $defaultServiceHost = $this->sandbox ? 'sandbox.evernote.com' : 'www.evernote.com';

        $options += array('serviceHost' => $defaultServiceHost);
        $this->serviceHost = $options['serviceHost'];

        $options += array('additionalHeaders' => array());
        $this->additionalHeaders = $options['additionalHeaders'];

        $this->token = isset($options['token']) ? $options['token'] : null;
        $this->secret = isset($options['secret']) ? $options['secret'] : null;
    }

    public function getRequestToken($callbackUrl)
    {
        $oauth = new \OAuth($this->consumerKey, $this->consumerSecret);

        return $oauth->getRequestToken($this->getEndpoint('oauth'), $callbackUrl);
    }

    public function getAccessToken($oauthToken, $oauthTokenSecret, $oauthVerifier)
    {
        $oauth = new \OAuth($this->consumerKey, $this->consumerSecret);
        $oauth->setToken($oauthToken, $oauthTokenSecret);
        $accessToken= $oauth->getAccessToken($this->getEndpoint('oauth'), null, $oauthVerifier);

        $this->token = $accessToken['oauth_token'];

        return $accessToken;
    }

    public function getAuthorizeUrl($requestToken)
    {
        $url = $this->getEndpoint('OAuth.action');
        $url .= '?oauth_token=';
        $url .= urlencode($requestToken);

        return $url;
    }

    public function getUserStore()
    {
        $userStoreUrl = $this->getEndpoint('/edam/user');

        return new Store($this->token, '\EDAM\UserStore\UserStoreClient', $userStoreUrl);
    }

    public function getNoteStore()
    {
        $userStore = $this->getUserStore();
        $noteStoreUrl = $userStore->getNoteStoreUrl();

        return new Store($this->token, '\EDAM\NoteStore\NoteStoreClient', $noteStoreUrl);
    }

    public function getSharedNoteStore($linkedNotebook)
    {
        $noteStoreUrl = $linkedNotebook->noteStoreUrl;
        $noteStore = new Store($this->token, '\EDAM\NoteStore\NoteStoreClient', $noteStoreUrl);
        $sharedAuth = $noteStore->authenticateToSharedNotebook($linkedNotebook->shareKey);
        $sharedToken = $sharedAuth->authenticationToken;

        return new Store($sharedToken, '\EDAM\NoteStore\NoteStoreClient', $noteStoreUrl);
    }

    public function getBusinessNoteStore()
    {
        $userStore = $this->getUserStore();
        $bizAuth = $userStore->authenticateToBusiness();
        $bizToken = $bizAuth->authenticationToken;
        $noteStoreUrl = $bizAuth->noteStoreUrl;

        return new Store($bizToken, '\EDAM\NoteStore\NoteStoreClient', $noteStoreUrl);
    }

    protected function getEndpoint($path = null)
    {
        $url = "https://".$this->serviceHost;
        if ($path != null) {
            $url .= "/".$path;
        }

        return $url;
    }

}

class Store
{
    private $token;
    private $userAgentId = '';
    private $client;

    public function __construct($token, $clientClass, $storeUrl)
    {
        $this->token = $token;
        if (preg_match(':A=(.+):', $token, $matches)) {
            $this->userAgentId = $matches[1];
        }
        $this->client = $this->getThriftClient($clientClass, $storeUrl);
    }

    public function __call($name, $arguments)
    {
        $method = new \ReflectionMethod($this->client, $name);
        $params = array();
        foreach ($method->getParameters() as $param) {
            $params[] = $param->name;
        }

        if (count($params) == count($arguments)) {
            return $method->invokeArgs($this->client, $arguments);
        } elseif (in_array('authenticationToken', $params)) {
            $newArgs = array();
            foreach ($method->getParameters() as $idx=>$param) {
                if ($param->name == 'authenticationToken') {
                    $newArgs[] = $this->token;
                }
                if ($idx < count($arguments)) {
                    $newArgs[] = $arguments[$idx];
                }
            }

            return $method->invokeArgs($this->client, $newArgs);
        } else {
            return $method->invokeArgs($this->client, $arguments);
        }
    }

    protected function getThriftClient($clientClass, $url)
    {
        $parts = parse_url($url);
        if (!isset($parts['port'])) {
            if ($parts['scheme'] === 'https') {
                $parts['port'] = 443;
            } else {
                $parts['port'] = 80;
            }
        }

        $httpClient = new \THttpClient(
            $parts['host'], $parts['port'], $parts['path'], $parts['scheme']);
        $httpClient->addHeaders(
            array('User-Agent' => $this->userAgentId.' / '.$this->getSdkVersion().'; PHP / '.phpversion()));
        $thriftProtocol = new \TBinaryProtocol($httpClient);

        return new $clientClass($thriftProtocol, $thriftProtocol);
    }

    protected function getSdkVersion()
    {
        $version = $GLOBALS['EDAM_UserStore_UserStore_CONSTANTS']['EDAM_VERSION_MAJOR']
            .'.'.$GLOBALS['EDAM_UserStore_UserStore_CONSTANTS']['EDAM_VERSION_MINOR'];

        return $version;
    }

}
