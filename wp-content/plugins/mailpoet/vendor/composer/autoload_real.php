<?php
if (!defined('ABSPATH')) exit;
// autoload_real.php @generated by Composer
class ComposerAutoloaderInita357c15004004bdfcb5ec7038ba4bc6c
{
 private static $loader;
 public static function loadClassLoader($class)
 {
 if ('Composer\Autoload\ClassLoader' === $class) {
 require __DIR__ . '/ClassLoader.php';
 }
 }
 public static function getLoader()
 {
 if (null !== self::$loader) {
 return self::$loader;
 }
 require __DIR__ . '/platform_check.php';
 spl_autoload_register(array('ComposerAutoloaderInita357c15004004bdfcb5ec7038ba4bc6c', 'loadClassLoader'), true, true);
 self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
 spl_autoload_unregister(array('ComposerAutoloaderInita357c15004004bdfcb5ec7038ba4bc6c', 'loadClassLoader'));
 require __DIR__ . '/autoload_static.php';
 call_user_func(\Composer\Autoload\ComposerStaticInita357c15004004bdfcb5ec7038ba4bc6c::getInitializer($loader));
 $loader->register(true);
 $includeFiles = \Composer\Autoload\ComposerStaticInita357c15004004bdfcb5ec7038ba4bc6c::$files;
 foreach ($includeFiles as $fileIdentifier => $file) {
 composerRequirea357c15004004bdfcb5ec7038ba4bc6c($fileIdentifier, $file);
 }
 return $loader;
 }
}
function composerRequirea357c15004004bdfcb5ec7038ba4bc6c($fileIdentifier, $file)
{
 if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
 $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
 require $file;
 }
}
