<?php

  /*
   * Copyright 2011-2012 Evernote Corporation.
   *
   * This file contains functions used by Evernote's PHP OAuth samples.
   */

  // Include the Evernote API from the lib subdirectory. 
  // lib simply contains the contents of /php/lib from the Evernote API SDK
  define("EVERNOTE_LIBS", dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib");
  ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . EVERNOTE_LIBS);

  require_once("Thrift.php");
  require_once("transport/TTransport.php");
  require_once("transport/THttpClient.php");
  require_once("protocol/TProtocol.php");
  require_once("protocol/TBinaryProtocol.php");
  require_once("packages/Types/Types_types.php");
  require_once("packages/UserStore/UserStore.php");
  require_once("packages/NoteStore/NoteStore.php");

  // Import the classes that we're going to be using
  use EDAM\NoteStore\NoteStoreClient;
  use EDAM\Error\EDAMSystemException, EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;

  // Verify that you successfully installed the PHP OAuth Extension
  if (!class_exists('OAuth')) {
    die("<span style=\"color:red\">The PHP OAuth Extension is not installed</span>");
  }

  // Verify that you have configured your API key
  if (strlen(OAUTH_CONSUMER_KEY) == 0 || strlen(OAUTH_CONSUMER_SECRET) == 0) {
    $configFile = dirname(__FILE__) . '/config.php';
    die("<span style=\"color:red\">Before using this sample code you must edit the file $configFile " .
        "and fill in OAUTH_CONSUMER_KEY and OAUTH_CONSUMER_SECRET with the values that you received from Evernote. " .
        "If you do not have an API key, you can request one from " .
        "<a href=\"http://dev.evernote.com/documentation/cloud/\">http://dev.evernote.com/documentation/cloud/</a></span>");
  }

  /*
   * The first step of OAuth authentication: the client (this application) 
   * obtains temporary credentials from the server (Evernote). 
   *
   * After successfully completing this step, the client has obtained the
   * temporary credentials identifier, an opaque string that is only meaningful 
   * to the server, and the temporary credentials secret, which is used in 
   * signing the token credentials request in step 3.
   *
   * This step is defined in RFC 5849 section 2.1:
   * http://tools.ietf.org/html/rfc5849#section-2.1
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  function getTemporaryCredentials() {
    global $lastError, $currentStatus;
    try {
      $oauth = new OAuth(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET);
      $requestTokenInfo = $oauth->getRequestToken(REQUEST_TOKEN_URL, getCallbackUrl());
      if ($requestTokenInfo) {
        $_SESSION['requestToken'] = $requestTokenInfo['oauth_token'];
        $_SESSION['requestTokenSecret'] = $requestTokenInfo['oauth_token_secret'];
        $currentStatus = 'Obtained temporary credentials';
        return TRUE;
      } else {
        $lastError = 'Failed to obtain temporary credentials: ' . $oauth->getLastResponse();
      }
    } catch (OAuthException $e) {
      $lastError = 'Error obtaining temporary credentials: ' . $e->getMessage();
    }
    return false;
  }

  /*
   * The completion of the second step in OAuth authentication: the resource owner 
   * authorizes access to their account and the server (Evernote) redirects them 
   * back to the client (this application).
   * 
   * After successfully completing this step, the client has obtained the
   * verification code that is passed to the server in step 3.
   *
   * This step is defined in RFC 5849 section 2.2:
   * http://tools.ietf.org/html/rfc5849#section-2.2
   *
   * @return boolean TRUE if the user authorized access, FALSE if they declined access.
   */
  function handleCallback() {
    global $lastError, $currentStatus;
    if (isset($_GET['oauth_verifier'])) {
      $_SESSION['oauthVerifier'] = $_GET['oauth_verifier'];
      $currentStatus = 'Content owner authorized the temporary credentials';
      return TRUE;
    } else {
      // If the User clicks "decline" instead of "authorize", no verification code is sent
      $lastError = 'Content owner did not authorize the temporary credentials';
      return FALSE;
    }
  }

  /*
   * The third and final step in OAuth authentication: the client (this application)
   * exchanges the authorized temporary credentials for token credentials.
   *
   * After successfully completing this step, the client has obtained the
   * token credentials that are used to authenticate to the Evernote API.
   * In this sample application, we simply store these credentials in the user's
   * session. A real application would typically persist them.
   *
   * This step is defined in RFC 5849 section 2.3:
   * http://tools.ietf.org/html/rfc5849#section-2.3
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  function getTokenCredentials() {
    global $lastError, $currentStatus;
    
    if (isset($_SESSION['accessToken'])) {
      $lastError = 'Temporary credentials may only be exchanged for token credentials once';
      return FALSE;
    }
    
    try {
      $oauth = new OAuth(OAUTH_CONSUMER_KEY, OAUTH_CONSUMER_SECRET);
      $oauth->setToken($_SESSION['requestToken'], $_SESSION['requestTokenSecret']);
      $accessTokenInfo = $oauth->getAccessToken(ACCESS_TOKEN_URL, null, $_SESSION['oauthVerifier']);
      if ($accessTokenInfo) {
        $_SESSION['accessToken'] = $accessTokenInfo['oauth_token'];
        $_SESSION['accessTokenSecret'] = $accessTokenInfo['oauth_token_secret'];
        $_SESSION['noteStoreUrl'] = $accessTokenInfo['edam_noteStoreUrl'];
        $_SESSION['webApiUrlPrefix'] = $accessTokenInfo['edam_webApiUrlPrefix'];
        // The expiration date is sent as a Java timestamp - milliseconds since the Unix epoch
        $_SESSION['tokenExpires'] = (int)($accessTokenInfo['edam_expires'] / 1000);
        $_SESSION['userId'] = $accessTokenInfo['edam_userId'];
        $currentStatus = 'Exchanged the authorized temporary credentials for token credentials';
        return TRUE;
      } else {
        $lastError = 'Failed to obtain token credentials: ' . $oauth->getLastResponse();
      }
    } catch (OAuthException $e) {
      $lastError = 'Error obtaining token credentials: ' . $e->getMessage();
    }  
    return FALSE;
  }
  
  /*
   * Demonstrate the use of token credentials obtained via OAuth by listing the notebooks
   * in the resource owner's Evernote account using the Evernote API. Returns an array
   * of String notebook names.
   *
   * Once you have obtained the token credentials identifier via OAuth, you can use it
   * as the auth token in any call to an Evernote API function.
   *
   * @return boolean TRUE on success, FALSE on failure
   */
  function listNotebooks() {
    global $lastError, $currentStatus;
    
    try {
  		$parts = parse_url($_SESSION['noteStoreUrl']);
      if (!isset($parts['port'])) {
        if ($parts['scheme'] === 'https') {
          $parts['port'] = 443;
        } else {
          $parts['port'] = 80;
        }
      }

      $noteStoreTrans = new THttpClient($parts['host'], $parts['port'], $parts['path'], $parts['scheme']);

      $noteStoreProt = new TBinaryProtocol($noteStoreTrans);
      $noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
      
      $authToken = $_SESSION['accessToken'];
      $notebooks = $noteStore->listNotebooks($authToken);
      $result = array();
      if (!empty($notebooks)) {
        foreach ($notebooks as $notebook) {
          $result[] = $notebook->name;
        }
      }
      $_SESSION['notebooks'] = $result;
      $currentStatus = 'Successfully listed content owner\'s notebooks';
      return TRUE;
    } catch (EDAMSystemException $e) {
      if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
        $lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
      } else {
        $lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
      }
    } catch (EDAMUserException $e) {
      if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
        $lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
      } else {
        $lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
      }
    } catch (EDAMNotFoundException $e) {
      if (isset(EDAMErrorCode::$__names[$e->errorCode])) {
        $lastError = 'Error listing notebooks: ' . EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter;
      } else {
        $lastError = 'Error listing notebooks: ' . $e->getCode() . ": " . $e->getMessage();
      }
    } catch (Exception $e) {
      $lastError = 'Error listing notebooks: ' . $e->getMessage();
    }
    return FALSE;
  }
  
  /*
   * Reset the current session.
   */
  function resetSession() {
    if (isset($_SESSION['requestToken'])) {
      unset($_SESSION['requestToken']);
    }
    if (isset($_SESSION['requestTokenSecret'])) {
      unset($_SESSION['requestTokenSecret']);
    }
    if (isset($_SESSION['oauthVerifier'])) {
      unset($_SESSION['oauthVerifier']);
    }
    if (isset($_SESSION['accessToken'])) {
      unset($_SESSION['accessToken']);
    }
    if (isset($_SESSION['accessTokenSecret'])) {
      unset($_SESSION['accessTokenSecret']);
    }
    if (isset($_SESSION['noteStoreUrl'])) {
      unset($_SESSION['noteStoreUrl']);
    }
    if (isset($_SESSION['webApiUrlPrefix'])) {
      unset($_SESSION['webApiUrlPrefix']);
    }
    if (isset($_SESSION['tokenExpires'])) {
    	unset($_SESSION['tokenExpires']);
    }
    if (isset($_SESSION['userId'])) {
    	unset($_SESSION['userId']);
    }
    if (isset($_SESSION['notebooks'])) {
      unset($_SESSION['notebooks']);
    }
  }
  
  /*
   * Get the URL of this application. This URL is passed to the server (Evernote)
   * while obtaining unauthorized temporary credentials (step 1). The resource owner 
   * is redirected to this URL after authorizing the temporary credentials (step 2).
   */
  function getCallbackUrl() {
    $thisUrl = (empty($_SERVER['HTTPS'])) ? "http://" : "https://";
    $thisUrl .= $_SERVER['SERVER_NAME'];
    $thisUrl .= ($_SERVER['SERVER_PORT'] == 80 || $_SERVER['SERVER_PORT'] == 443) ? "" : (":".$_SERVER['SERVER_PORT']);
    $thisUrl .= $_SERVER['SCRIPT_NAME'];
    $thisUrl .= '?action=callback';
    return $thisUrl;
  }
  
  /*
   * Get the Evernote server URL used to authorize unauthorized temporary credentials.
   */
  function getAuthorizationUrl() {
    $url = AUTHORIZATION_URL;
    $url .= '?oauth_token=';
    $url .= urlencode($_SESSION['requestToken']);
    return $url;
  }  
?>
