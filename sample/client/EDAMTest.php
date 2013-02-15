<?php
//
// A simple command-line Evernote API demo script that lists all notebooks in
// the user's account and creates a simple test note in the default notebook.
//
// Before running this sample, you must fill in your Evernote developer token.
//
// To run:
//   php EDAMTest.php
//

// Import the classes that we're going to be using
use EDAM\Types\Data, EDAM\Types\Note, EDAM\Types\Resource, EDAM\Types\ResourceAttributes;
use EDAM\Error\EDAMUserException, EDAM\Error\EDAMErrorCode;
use Evernote\Client;

ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . "../../lib" . PATH_SEPARATOR);
require_once 'autoload.php';

require_once 'Evernote/Client.php';

require_once 'packages/Errors/Errors_types.php';
require_once 'packages/Types/Types_types.php';
require_once 'packages/Limits/Limits_constants.php';

// A global exception handler for our program so that error messages all go to the console
function en_exception_handler($exception)
{
    echo "Uncaught " . get_class($exception) . ":\n";
    if ($exception instanceof EDAMUserException) {
        echo "Error code: " . EDAMErrorCode::$__names[$exception->errorCode] . "\n";
        echo "Parameter: " . $exception->parameter . "\n";
    } elseif ($exception instanceof EDAMSystemException) {
        echo "Error code: " . EDAMErrorCode::$__names[$exception->errorCode] . "\n";
        echo "Message: " . $exception->message . "\n";
    } else {
        echo $exception;
    }
}
set_exception_handler('en_exception_handler');

// Real applications authenticate with Evernote using OAuth, but for the
// purpose of exploring the API, you can get a developer token that allows
// you to access your own Evernote account. To get a developer token, visit
// https://sandbox.evernote.com/api/DeveloperToken.action
$authToken = "your developer token";

if ($authToken == "your developer token") {
    print "Please fill in your developer token\n";
    print "To get a developer token, visit https://sandbox.evernote.com/api/DeveloperToken.action\n";
    exit(1);
}

// Initial development is performed on our sandbox server. To use the production
// service, change "sandbox.evernote.com" to "www.evernote.com" and replace your
// developer token above with a token from
// https://www.evernote.com/api/DeveloperToken.action
$client = new Client(array('token' => $authToken));

$userStore = $client->getUserStore();

// Connect to the service and check the protocol version
$versionOK =
    $userStore->checkVersion("Evernote EDAMTest (PHP)",
         $GLOBALS['EDAM_UserStore_UserStore_CONSTANTS']['EDAM_VERSION_MAJOR'],
         $GLOBALS['EDAM_UserStore_UserStore_CONSTANTS']['EDAM_VERSION_MINOR']);
print "Is my Evernote API version up to date?  " . $versionOK . "\n\n";
if ($versionOK == 0) {
    exit(1);
}

$noteStore = $client->getNoteStore();

// List all of the notebooks in the user's account
$notebooks = $noteStore->listNotebooks();
print "Found " . count($notebooks) . " notebooks\n";
foreach ($notebooks as $notebook) {
    print "    * " . $notebook->name . "\n";
}

print"\nCreating a new note in the default notebook\n\n";

// To create a new note, simply create a new Note object and fill in
// attributes such as the note's title.
$note = new Note();
$note->title = "Test note from EDAMTest.php";

// To include an attachment such as an image in a note, first create a Resource
// for the attachment. At a minimum, the Resource contains the binary attachment
// data, an MD5 hash of the binary data, and the attachment MIME type. It can also
// include attributes such as filename and location.
$filename = "enlogo.png";
$image = fread(fopen($filename, "rb"), filesize($filename));
$hash = md5($image, 1);

$data = new Data();
$data->size = strlen($image);
$data->bodyHash = $hash;
$data->body = $image;

$resource = new Resource();
$resource->mime = "image/png";
$resource->data = $data;
$resource->attributes = new ResourceAttributes();
$resource->attributes->fileName = $filename;

// Now, add the new Resource to the note's list of resources
$note->resources = array( $resource );

// To display the Resource as part of the note's content, include an <en-media>
// tag in the note's ENML content. The en-media tag identifies the corresponding
// Resource using the MD5 hash.
$hashHex = md5($image, 0);

// The content of an Evernote note is represented using Evernote Markup Language
// (ENML). The full ENML specification can be found in the Evernote API Overview
// at http://dev.evernote.com/documentation/cloud/chapters/ENML.php
$note->content =
    '<?xml version="1.0" encoding="UTF-8"?>' .
    '<!DOCTYPE en-note SYSTEM "http://xml.evernote.com/pub/enml2.dtd">' .
    '<en-note>Here is the Evernote logo:<br/>' .
    '<en-media type="image/png" hash="' . $hashHex . '"/>' .
    '</en-note>';

// When note titles are user-generated, it's important to validate them
$len = strlen($note->title);
$min = $GLOBALS['EDAM_Limits_Limits_CONSTANTS']['EDAM_NOTE_TITLE_LEN_MIN'];
$max = $GLOBALS['EDAM_Limits_Limits_CONSTANTS']['EDAM_NOTE_TITLE_LEN_MAX'];
$pattern = '#' . $GLOBALS['EDAM_Limits_Limits_CONSTANTS']['EDAM_NOTE_TITLE_REGEX'] . '#'; // Add PCRE delimiters
if ($len < $min || $len > $max || !preg_match($pattern, $note->title)) {
    print "\nInvalid note title: " . $note->title . '\n\n';
    exit(1);
}

// Finally, send the new note to Evernote using the createNote method
// The new Note object that is returned will contain server-generated
// attributes such as the new note's unique GUID.
$createdNote = $noteStore->createNote($note);

print "Successfully created a new note with GUID: " . $createdNote->guid . "\n";
