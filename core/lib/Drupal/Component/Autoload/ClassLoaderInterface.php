<?php

namespace Drupal\Component\Autoload;


interface ClassLoaderInterface {

  public function getClassMap();

  /**
   * @param array $classMap Class to filename map
   */
  public function addClassMap(array $classMap);

  /**
   * Registers a set of classes, merging with any others previously set.
   *
   * @param string       $prefix  The classes prefix
   * @param array|string $paths   The location(s) of the classes
   * @param bool         $prepend Prepend the location(s)
   */
  public function add($prefix, $paths, $prepend = false);

  public function addMultiple($prefixes, $prepend = false);

  public function composerVendorDir($dir);

  /**
   * @param $extensionName
   * @param $relativeExtensionDir
   * @param bool $prepend
   */
  public function addDrupalExtension($extensionName, $relativeExtensionDir, $prepend = FALSE);

  /**
   * @param $extensionName
   * @param $relativeExtensionDir
   * @param bool $prepend
   */
  public function addDrupalExtensionTests($extensionName, $relativeExtensionDir, $prepend = false);

  /**
   * @param array $extensions
   * @param bool $prepend
   */
  public function addDrupalExtensionsByRelativeFilePath($extensions, $prepend = FALSE);

  /**
   * Turns on searching the include path for class files.
   *
   * @param bool $useIncludePath
   */
  public function setUseIncludePath($useIncludePath);

  /**
   * Can be used to check if the autoloader uses the include path to check
   * for classes.
   *
   * @return bool
   */
  public function getUseIncludePath();

  /**
   * Registers this instance as an autoloader.
   *
   * @param bool $prepend Whether to prepend the autoloader or not
   */
  public function register($prepend = false);

  /**
   * Unregisters this instance as an autoloader.
   */
  public function unregister();

  /**
   * Loads the given class or interface.
   *
   * @param string $class
   *   The name of the class
   * @return bool
   *   FALSE, if not found.
   *   TRUE, if loaded and $returnFile was FALSE.
   */
  public function loadClass($class);

  public function findFile($class);
}