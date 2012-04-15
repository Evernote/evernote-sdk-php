<?php

  /*
   * Copyright 2010-2012 Evernote Corporation.
   *
   * This sample web application demonstrates the step-by-step process of using OAuth to 
   * authenticate to the Evernote web service. More information can be found in the 
   * Evernote API Overview at http://dev.evernote.com/documentation/cloud/.
   *
   * This application uses the PHP OAuth Extension to implement an OAuth client.
   * To use the application, you must install the PHP OAuth Extension as described
   * in the extension's documentation: http://www.php.net/manual/en/book.oauth.php
   *
   * Note that the formalization of OAuth as RFC 5849 introduced some terminology changes.
   * The comments in this sample code use the new (RFC) terminology, but most of the code
   * itself still uses the old terms, which are also used by the PHP OAuth Extension.
   *
   * Old term                    New Term
   * --------------------------------------------------
   * Consumer                    client
   * Service Provider            server
   * User                        resource owner
   * Consumer Key and Secret     client credentials
   * Request Token and Secret    temporary credentials
   * Access Token and Secret     token credentials
   */

  // Include our configuration settings
  require_once('config.php');
  
  // Include our OAuth functions
  require_once('functions.php');
  
  // Use a session to keep track of temporary credentials, etc
  session_start();
  
  // Status variables
  $lastError = null;
  $currentStatus = null;
  
  // Request dispatching. If a function fails, $lastError will be updated.
  if (isset($_GET['action'])) {
    $action = $_GET['action'];
    if ($action == 'requestToken') {
      getTemporaryCredentials();
    } else if ($action == 'callback') {
      handleCallback();
    } else if ($action == 'accessToken') {
      getTokenCredentials();
    } else if ($action == 'listNotebooks') {
      listNotebooks();
    } else if ($action == 'reset') {
      resetSession();
    }
  }
?>

<html>
  <head>
    <title>Evernote PHP OAuth Demo</title>
  </head>
  <body>

    <h1>Evernote PHP OAuth Demo</h1>

    <p>
      This application demonstrates the use of OAuth to authenticate to the Evernote web service.
      OAuth support is implemented using the <a href="http://www.php.net/manual/en/book.oauth.php">PHP OAuth Extension</a>.
    </p>

    <p>
      On this page, we demonstrate each step of the OAuth authentication process.
      You would not typically expose a user to this level of detail.
      <a href="sampleApp.php?action=reset">Click here</a> to use an application that is more similar to what you would use in the real world.
    </p>

    <hr/>

    <h2>Authentication Steps</h2>

    <ul>

      <!-- Step 1: get temporary credentials -->
      <li><b>Step 1</b>: 
<?php if (!isset($_SESSION['requestToken'])) { ?>
      <a href="index.php?action=requestToken">Click here</a> to
<?php } ?>
      obtain temporary credentials

      <!-- Step 2: authorize the temporary credentials -->
      <li><b>Step 2</b>: 
<?php if (isset($_SESSION['requestToken']) && !isset($_SESSION['oauthVerifier'])) { ?>
      <a href="<?php echo getAuthorizationUrl(); ?>">Click here</a> to
<?php } ?>
      authorize the temporary credentials
            
      <!-- Step 3: exchange the authorized temporary credentials for token credentials -->
      <li><b>Step 3</b>: 
<?php if (isset($_SESSION['requestToken']) && isset($_SESSION['oauthVerifier']) && !isset($_SESSION['accessToken'])) { ?>
      <a href="index.php?action=accessToken">Click here</a> to
<?php } ?>
      exchange the authorized temporary credentials for token credentials

      <!-- Step 4: demonstrate using the token credentials to access the user's account -->
      <li><b>Step 4</b>: 
<?php if (isset($_SESSION['accessToken']) && !isset($_SESSION['notebooks'])) { ?>
      <a href="index.php?action=listNotebooks">Click here</a> to
<?php } ?>
      list all notebooks in the authorizing user's Evernote account

    </ul>

    <p>
      <a href="index.php?action=reset">Click here</a> to start over
    </p>

    <hr/>
    
    <h2>Current status</h2>
    <p>
      <b>Evernote server:</b> <?php echo EVERNOTE_SERVER; ?>
      <br/>
      <b>NoteStore URL:</b> <?php echo $_SESSION['noteStoreUrl']; ?>
      <br/>
      <b>Web API URL prefix:</b> <?php echo $_SESSION['webApiUrlPrefix']; ?>
		</p>
    <p>
      <b>Last action:</b> 
<?php
    if (!empty($lastError)) {
      echo '<span style="color:red">' . $lastError . '</span>';
    } else {
      echo '<span style="color:green">' . $currentStatus . '</span>';
    }
?>
    </p>
    
<?php if (isset($_SESSION['notebooks'])) { ?>
    <b>Notebooks:</b>
    <ul>
<?php foreach ($_SESSION['notebooks'] as $notebook) { ?>
      <li><?php print $notebook; ?></li>
<?php } ?>
    </ul>
<?php } ?>

    <b>Temporary credentials:</b>
    <ul>
      <li><b>Identifier:</b><br/><?php if (isset($_SESSION['requestToken'])) { echo $_SESSION['requestToken']; } ?>
      <li><b>Secret:</b><br/><?php if (isset($_SESSION['requestTokenSecret'])) { echo $_SESSION['requestTokenSecret']; } ?>
    </ul>

    <p>
      <b>OAuth verifier:</b><br/>
      <?php if (isset($_SESSION['oauthVerifier'])) { echo $_SESSION['oauthVerifier']; } ?>
    </p>

    <b>Token credentials:</b>
    <ul>
      <li><b>Identifier:</b><br/><?php if (isset($_SESSION['accessToken'])) { echo $_SESSION['accessToken']; } ?>
      <li><b>Secret:</b><br/><?php if (isset($_SESSION['accessTokenSecret'])) { echo $_SESSION['accessTokenSecret']; } ?>
      <li><b>User ID:</b><br/><?php if (isset($_SESSION['userId'])) { echo $_SESSION['userId']; } ?>
      <li><b>Expires:</b><br/><?php if (isset($_SESSION['tokenExpires'])) { echo date(DATE_RFC1123, $_SESSION['tokenExpires']); } ?>
    </ul>

  </body>
</html>

