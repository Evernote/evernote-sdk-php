<?php

    /*
     * Copyright 2010-2012 Evernote Corporation.
     *
     * This sample web application demonstrates the step-by-step process of using OAuth to
     * authenticate to the Evernote web service. More information can be found in the
     * Evernote API Overview at http://dev.evernote.com/documentation/cloud/.
     *
     */

    // Include our configuration settings
    require_once 'config.php';

    // Include our OAuth functions
    require_once 'functions.php';

    // Use a session to keep track of temporary credentials, etc
    session_start();

    // Status variables
    $lastError = null;
    $currentStatus = null;

    // Request dispatching. If a function fails, $lastError will be updated.
    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        if ($action == 'reset') {
            resetSession();
        }
    }

    // Store auth in a session
    if (isset($_SESSION['opauth'])) {
        $_SESSION['accessToken'] = $_SESSION['opauth']['auth']['credentials']['token'];
        $_SESSION['userId'] = $_SESSION['opauth']['auth']['info']['userId'];
        $_SESSION['tokenExpires'] = $_SESSION['opauth']['auth']['info']['expires'];
        $currentStatus = 'Congratulations, you have successfully authorized this application to access your Evernote account!';
        listNotebooks();
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
            OAuth support is implemented using the <a href="http://opauth.org/">Opauth</a>.
        </p>

        <hr/>

        <h2>Authentication</h2>

        <ul>
<?php if (isset($_SESSION['accessToken'])) { ?>
            <li>
                <a href="/?action=reset">Click here</a> to start over
            </li>
<?php } else { ?>
            <li>
                <a href="/evernote">Click here</a> to authenticate
            </li>
<?php } ?>
        </ul>

        <hr/>

        <h2>Current status</h2>
        <p>
            <b>Evernote server:</b> <?php echo htmlspecialchars(SANDBOX ? 'sandbox' : 'production');     ?>

            <b>Last action:</b>
<?php
    if (!empty($lastError)) {
        echo '<span style="color:red">' . htmlspecialchars($lastError) . '</span>';
    } else {
        echo '<span style="color:green">' . htmlspecialchars($currentStatus) . '</span>';
    }
?>
        </p>

<?php if (isset($_SESSION['notebooks'])) { ?>
        <b>Notebooks:</b>
        <ul>
<?php foreach ($_SESSION['notebooks'] as $notebook) { ?>
            <li>
    <?php echo htmlspecialchars($notebook); ?>
            </li>
    <?php } ?>
        </ul>
<?php } ?>

        <b>Token credentials:</b>
        <ul>
            <li><b>Identifier:</b><br/>
<?php if (isset($_SESSION['accessToken'])) { echo htmlspecialchars($_SESSION['accessToken']); } ?>
            </li>
            <li><b>Secret:</b><br/>
<?php if (isset($_SESSION['accessTokenSecret'])) { echo htmlspecialchars($_SESSION['accessTokenSecret']); } ?>
            </li>
            <li><b>User ID:</b><br/>
<?php if (isset($_SESSION['userId'])) { echo htmlspecialchars($_SESSION['userId']); } ?>
            </li>
            <li><b>Expires:</b><br/>
<?php if (isset($_SESSION['tokenExpires'])) { echo htmlspecialchars(date(DATE_RFC1123, $_SESSION['tokenExpires'])); } ?>
            </li>
        </ul>

    </body>
</html>
