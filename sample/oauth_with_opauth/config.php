<?php

    /*
     * Copyright 2010-2012 Evernote Corporation.
     *
     * This file contains configuration information for Evernote's PHP OAuth samples.
     * Before running the sample code, you must change the client credentials below.
     */

    /**
     * Composer autoload
     */
    require 'vendor/autoload.php';

    // Replace this value with FALSE to use Evernote's production server
    define('SANDBOX', TRUE);

    /**
     * Opauth configuration
     */
    $config = array(
      'path' => '/',
      'callback_url' => '{path}',
      'security_salt' => 'LDFmiilYf8Fyw5W10rx4W1KsVrieQCnpBzzpTBWA5vJidQKDx8pMJbmw28R1C4n',
      'Strategy' => array(
        'Evernote' => array(
          'api_key' => 'YOUR API CONSUMER KEY',
          'secret_key' => 'YOUR API CONSUMER SECRET',
          'sandbox' => SANDBOX // optional
        ),
      ),
    );
    $Opauth = new Opauth($config);
