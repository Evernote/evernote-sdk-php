<?php
//
// A simple Evernote API demo application that authenticates with the
// Evernote web service, lists all notebooks in the user's account,
// and creates a simple test note in the default notebook.
//
// Before running this sample, you must change the API consumer key
// and consumer secret to the values that you received from Evernote.
//
// To run:
//   php EDAMTest.php myuser mypass
//

// Import the classes that we're going to be using
use EDAM\UserStore\UserStoreClient;
use EDAM\NoteStore\NoteStoreClient;
use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Resource, EDAM\Types\ResourceAttributes;
use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../lib" . PATH_SEPARATOR);
require_once("autoload.php");

require_once("Thrift.php");
require_once("transport/TTransport.php");
require_once("transport/THttpClient.php");
require_once("protocol/TProtocol.php");
require_once("protocol/TBinaryProtocol.php");

require_once("packages/Errors/Errors_types.php");
require_once("packages/Types/Types_types.php");
require_once("packages/UserStore/UserStore.php");
require_once("packages/UserStore/UserStore_constants.php");
require_once("packages/NoteStore/NoteStore.php");
require_once("packages/Limits/Limits_constants.php");


// NOTE: Provide the consumer key and consumer secret that you received from Evernote
$consumerKey = "";
$consumerSecret = "";

if (empty($consumerKey) || empty($consumerSecret)) {
  print "Please set your API consumer key and secret\n";
  print "To get an API key, visit http://dev.evernote.com/documentation/cloud/\n";
  exit(1);
}
if ($argc < 3) {
  print "Arguments:  <username> <password>\n";
  exit(1);
}
$username = $argv[1];
$password = $argv[2];

$evernoteHost = "sandbox.evernote.com";
$evernotePort = "443";
$evernoteScheme = "https";

$userStoreHttpClient =
  new THttpClient($evernoteHost, $evernotePort, "/edam/user", $evernoteScheme);
$userStoreProtocol = new TBinaryProtocol($userStoreHttpClient);
$userStore = new UserStoreClient($userStoreProtocol, $userStoreProtocol);

// Connect to the service and check the protocol version
$versionOK =
  $userStore->checkVersion("PHP EDAMTest",
			   $GLOBALS['EDAM_UserStore_UserStore_CONSTANTS']['EDAM_VERSION_MAJOR'],
			   $GLOBALS['EDAM_UserStore_UserStore_CONSTANTS']['EDAM_VERSION_MINOR']);
print "Is my EDAM protocol version up to date?  " . $versionOK . "\n\n";
if ($versionOK == 0) {
  exit(1);
}

// Authenticate the user
try {
  $authResult = $userStore->authenticate($username, $password, $consumerKey, $consumerSecret);
} catch (EDAMUserException $e) {
  // See http://www.evernote.com/about/developer/api/ref/UserStore.html#Fn_UserStore_authenticate
  $parameter = $e->parameter;
  $errorCode = $e->errorCode;
  $errorText = EDAMErrorCode::$__names[$errorCode];

  echo "Authentication failed (parameter: $parameter errorCode: $errorText)\n";

  if ($errorCode == EDAMErrorCode::INVALID_AUTH) {
    if ($parameter == "consumerKey") {
      echo "Your consumer key was not accepted by $evernoteHost\n";
      echo "This sample client application requires a client API key.\nIf you requested an OAuth key, you must authenticate using OAuth as shown in sample/oauth\n";
      echo "If you do not have an API Key from Evernote, you can request one from http://dev.evernote.com/documentation/cloud/\n";
    } else if ($parameter == "username") {
      echo "You must authenticate using a username and password from $evernoteHost\n";
      if ($evernoteHost != "www.evernote.com") {
        echo "Note that your production Evernote account will not work on $evernoteHost,\n" .
             "you must register for a separate test account at https://$evernoteHost/Registration.action\n";
      }
    } else if ($parameter == "password") {
      echo "The password that you entered is incorrect\n";
    }
  }

  echo "\n";
  exit(1);
}

$user = $authResult->user;
$authToken = $authResult->authenticationToken;
print "Authentication was successful for " . $user->username . "\n";
print "Authentication token = " . $authToken . "\n";

$parts = parse_url($authResult->noteStoreUrl);
if (!isset($parts['port'])) {
  if ($parts['scheme'] === 'https') {
    $parts['port'] = 443;
  } else {
    $parts['port'] = 80;
  }
}
$noteStoreHttpClient = 
  new THttpClient($parts['host'], $parts['port'], $parts['path'], $parts['scheme']);
$noteStoreProtocol = new TBinaryProtocol($noteStoreHttpClient);
$noteStore = new NoteStoreClient($noteStoreProtocol, $noteStoreProtocol);

$notebooks = $noteStore->listNotebooks($authToken);
print "Found " . count($notebooks) . " notebooks\n";
foreach ($notebooks as $notebook) {
  print "    * " . $notebook->name . "\n";
  if ($notebook->defaultNotebook) {
    $defaultNotebook = $notebook;
  }
}

print"\nCreating a new note in default notebook:  " . $defaultNotebook->name . "\n\n";

$filename = "enlogo.png";
$image = fread(fopen($filename, "rb"), filesize($filename));
$hash = md5($image, 1);
$hashHex = md5($image, 0);

$data = new Data();
$data->size = strlen($image);
$data->bodyHash = $hash;
$data->body = $image;

$resource = new Resource();
$resource->mime = "image/png";
$resource->data = $data;
$resource->attributes = new ResourceAttributes();
$resource->attributes->fileName = $filename;

$note = new Note();
$note->title = "Test note from EDAMTest.php";
$note->content =
  '<?xml version="1.0" encoding="UTF-8"?>' .
  '<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">' .
  '<en-note>Here is the Evernote logo:<br/>' .
  '<en-media type="image/png" hash="' . $hashHex . '"/>' .
  '</en-note>';
$note->resources = array( $resource );

// When note titles are user-generated, it's important to validate them
$len = strlen($note->title);
$min = $GLOBALS['EDAM_Limits_Limits_CONSTANTS']['EDAM_NOTE_TITLE_LEN_MIN'];
$max = $GLOBALS['EDAM_Limits_Limits_CONSTANTS']['EDAM_NOTE_TITLE_LEN_MAX'];
$pattern = '#' . $GLOBALS['EDAM_Limits_Limits_CONSTANTS']['EDAM_NOTE_TITLE_REGEX'] . '#'; // Add PCRE delimiters
if ($len < $min || $len > $max || !preg_match($pattern, $note->title)) {
  print "\nInvalid note title: " . $note->title . '\n\n';
  exit(1);
}

$createdNote = $noteStore->createNote($authToken, $note);

print "Successfully created a new note with GUID: " . $createdNote->guid . "\n";

?>
