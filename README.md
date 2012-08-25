Evernote SDK for PHP
=========================================

Evernote API version 1.22

Overview
--------
This SDK contains wrapper code used to call the Evernote Cloud API from PHP.

The SDK also contains two samples. The code in sample/client demonstrates the basic use of the SDK for single-user scripts. The code in sample/oauth demonstrates the basic use of the SDK for web applications that authenticate using OAuth.

Prerequisites
-------------
This SDK makes use of PHP namespaces, and as such requires PHP 5.3 or later.

In order to use the code in this SDK, you need to obtain an API key from http://dev.evernote.com/documentation/cloud. You'll also find full API documentation on that page.

In order to run the sample code, you need a user account on the sandbox service where you will do your development. Sign up for an account at https://sandbox.evernote.com/Registration.action 

In order to run the client client sample code, you need a developer token. Get one at https://sandbox.evernote.com/api/DeveloperToken.action

Getting Started - Client
------------------------
The code in sample/client/EDAMTest.php demonstrates the basics of using the Evernote API, using developer tokens to simplify the authentication process while you're learning. 

1. Open sample/client/EDAMTest.php
2. Scroll down and fill in your Evernote developer token.
3. On the command line, run the following command to execute the script:

    php EDAMTest.php

Getting Started - OAuth
-----------------------
Web applications must use OAuth to authenticate to the Evernote service. The code in sample/oauth contains a simple web app that demonstrates the OAuth authentication process.

1. Open the file sample/oauth/config.php
2. Fill in your Evernote API consumer key and secret.
3. Deploy the sample/oauth directory to your web server
4. Load the web application in your browser (e.g. http://localhost/oauth)

There are two pages in the sample. index.php demonstrates each step of the OAuth process in detail. This is useful for developers, but not what an end user would see. sampleApp.php demonstrates the simplified process, which is similar to what you would implement in your production app.
