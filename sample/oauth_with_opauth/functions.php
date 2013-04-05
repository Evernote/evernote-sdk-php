<?php

    /*
     * Copyright 2011-2012 Evernote Corporation.
     *
     * This file contains functions used by Evernote's PHP OAuth samples.
     */

    // Import the classes that we're going to be using
    use EDAM\Error\EDAMSystemException,
        EDAM\Error\EDAMUserException,
        EDAM\Error\EDAMErrorCode,
        EDAM\Error\EDAMNotFoundException;
    use Evernote\Client;

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
    function listNotebooks()
    {
        global $lastError, $currentStatus;

        try {
            $accessToken = $_SESSION['accessToken'];
            $client = new Client(array(
                'token' => $accessToken,
                'sandbox' => SANDBOX
            ));
            $notebooks = $client->getNoteStore()->listNotebooks();
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
    function resetSession()
    {
        if (isset($_SESSION['opauth'])) {
            unset($_SESSION['opauth']);
        }
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
