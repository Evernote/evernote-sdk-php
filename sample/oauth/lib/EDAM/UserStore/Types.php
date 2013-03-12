<?php
namespace EDAM\UserStore;

/**
 * Autogenerated by Thrift Compiler (0.9.0-dev)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Exception\TApplicationException;


class PublicUserInfo extends TBase {
  static $_TSPEC;

  public $userId = null;
  public $shardId = null;
  public $privilege = null;
  public $username = null;
  public $noteStoreUrl = null;
  public $webApiUrlPrefix = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'userId',
          'type' => TType::I32,
          ),
        2 => array(
          'var' => 'shardId',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'privilege',
          'type' => TType::I32,
          ),
        4 => array(
          'var' => 'username',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'noteStoreUrl',
          'type' => TType::STRING,
          ),
        6 => array(
          'var' => 'webApiUrlPrefix',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'PublicUserInfo';
  }

  public function read($input)
  {
    return $this->_read('PublicUserInfo', self::$_TSPEC, $input);
  }
  public function write($output) {
    return $this->_write('PublicUserInfo', self::$_TSPEC, $output);
  }
}

class AuthenticationResult extends TBase {
  static $_TSPEC;

  public $currentTime = null;
  public $authenticationToken = null;
  public $expiration = null;
  public $user = null;
  public $publicUserInfo = null;
  public $noteStoreUrl = null;
  public $webApiUrlPrefix = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'currentTime',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'authenticationToken',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'expiration',
          'type' => TType::I64,
          ),
        4 => array(
          'var' => 'user',
          'type' => TType::STRUCT,
          'class' => '\EDAM\Types\User',
          ),
        5 => array(
          'var' => 'publicUserInfo',
          'type' => TType::STRUCT,
          'class' => '\EDAM\UserStore\PublicUserInfo',
          ),
        6 => array(
          'var' => 'noteStoreUrl',
          'type' => TType::STRING,
          ),
        7 => array(
          'var' => 'webApiUrlPrefix',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'AuthenticationResult';
  }

  public function read($input)
  {
    return $this->_read('AuthenticationResult', self::$_TSPEC, $input);
  }
  public function write($output) {
    return $this->_write('AuthenticationResult', self::$_TSPEC, $output);
  }
}

class BootstrapSettings extends TBase {
  static $_TSPEC;

  public $serviceHost = null;
  public $marketingUrl = null;
  public $supportUrl = null;
  public $accountEmailDomain = null;
  public $enableFacebookSharing = null;
  public $enableGiftSubscriptions = null;
  public $enableSupportTickets = null;
  public $enableSharedNotebooks = null;
  public $enableSingleNoteSharing = null;
  public $enableSponsoredAccounts = null;
  public $enableTwitterSharing = null;
  public $enableLinkedInSharing = null;
  public $enablePublicNotebooks = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'serviceHost',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'marketingUrl',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'supportUrl',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'accountEmailDomain',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'enableFacebookSharing',
          'type' => TType::BOOL,
          ),
        6 => array(
          'var' => 'enableGiftSubscriptions',
          'type' => TType::BOOL,
          ),
        7 => array(
          'var' => 'enableSupportTickets',
          'type' => TType::BOOL,
          ),
        8 => array(
          'var' => 'enableSharedNotebooks',
          'type' => TType::BOOL,
          ),
        9 => array(
          'var' => 'enableSingleNoteSharing',
          'type' => TType::BOOL,
          ),
        10 => array(
          'var' => 'enableSponsoredAccounts',
          'type' => TType::BOOL,
          ),
        11 => array(
          'var' => 'enableTwitterSharing',
          'type' => TType::BOOL,
          ),
        12 => array(
          'var' => 'enableLinkedInSharing',
          'type' => TType::BOOL,
          ),
        13 => array(
          'var' => 'enablePublicNotebooks',
          'type' => TType::BOOL,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'BootstrapSettings';
  }

  public function read($input)
  {
    return $this->_read('BootstrapSettings', self::$_TSPEC, $input);
  }
  public function write($output) {
    return $this->_write('BootstrapSettings', self::$_TSPEC, $output);
  }
}

class BootstrapProfile extends TBase {
  static $_TSPEC;

  public $name = null;
  public $settings = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'name',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'settings',
          'type' => TType::STRUCT,
          'class' => '\EDAM\UserStore\BootstrapSettings',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'BootstrapProfile';
  }

  public function read($input)
  {
    return $this->_read('BootstrapProfile', self::$_TSPEC, $input);
  }
  public function write($output) {
    return $this->_write('BootstrapProfile', self::$_TSPEC, $output);
  }
}

class BootstrapInfo extends TBase {
  static $_TSPEC;

  public $profiles = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'profiles',
          'type' => TType::LST,
          'etype' => TType::STRUCT,
          'elem' => array(
            'type' => TType::STRUCT,
            'class' => '\EDAM\UserStore\BootstrapProfile',
            ),
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'BootstrapInfo';
  }

  public function read($input)
  {
    return $this->_read('BootstrapInfo', self::$_TSPEC, $input);
  }
  public function write($output) {
    return $this->_write('BootstrapInfo', self::$_TSPEC, $output);
  }
}

class Constants
{
    public static $EDAM_VERSION_MAJOR = 1;

    public static $EDAM_VERSION_MINOR = 23;
}


