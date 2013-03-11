<?php
use Thrift\ClassLoader\ThriftClassLoader;

require_once __DIR__ . '/Thrift/ClassLoader/ThriftClassLoader.php';

$loader = new ThriftClassLoader();
$loader->registerNamespace('Thrift', __DIR__ );
$loader->registerDefinition('EDAM', __DIR__);
$loader->registerNamespace('Evernote', __DIR__ );
$loader->register();
