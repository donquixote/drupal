<?php

namespace Drupal\Core\ClassLoader;


abstract class AbstractClassLoader implements ClassLoaderInterface {

  protected $useIncludePath = false;
  protected $classMap = array();

  public function getClassMap() {
    return $this->classMap;
  }

  /**
   * @param array $classMap Class to filename map
   */
  public function addClassMap(array $classMap) {
    if ($this->classMap) {
      $this->classMap = array_merge($this->classMap, $classMap);
    } else {
      $this->classMap = $classMap;
    }
  }

  public function addMultiple($prefixes, $prepend = FALSE) {
    foreach ($prefixes as $prefix => $paths) {
      $this->add($prefix, $paths, $prepend);
    }
  }

  public function composerVendorDir($dir, $complete = TRUE) {
    if ($complete) {
      // This does not also register classmap and namespaces,
      // but also set include paths, and include some files by default.
      /**
       * @var \Composer\Autoload\ClassLoader $composerLoader
       */
      $composerLoader = $dir . '/autoload.php';
      $composerLoader->unregister();
      $this->add('', $composerLoader->getFallbackDirs());
      $this->setUseIncludePath($composerLoader->getUseIncludePath());
      $prefixes = $composerLoader->getPrefixes();
      $classMap = $composerLoader->getClassMap();
    }
    else {
      $prefixes = require $dir . '/composer/autoload_namespaces.php';
      $classMap = require $dir . '/composer/autoload_classmap.php';
    }
    foreach ($prefixes as $prefix => $path) {
      $this->add($prefix, $path);
    }
    if ($classMap) {
      $this->addClassMap($classMap);
    }
  }

  /**
   * @inheritdoc
   */
  public function addDrupalExtensionsByRelativeFilePath($extensions, $prepend = FALSE) {
    foreach ($extensions as $extension_name => $relativeFilePath) {
      $this->addDrupalExtension($extension_name, dirname($relativeFilePath), $prepend);
    }
  }

  /**
   * Turns on searching the include path for class files.
   *
   * @param bool $useIncludePath
   */
  public function setUseIncludePath($useIncludePath) {
    $this->useIncludePath = $useIncludePath;
  }

  /**
   * Can be used to check if the autoloader uses the include path to check
   * for classes.
   *
   * @return bool
   */
  public function getUseIncludePath() {
    return $this->useIncludePath;
  }

  /**
   * Registers this instance as an autoloader.
   *
   * @param bool $prepend Whether to prepend the autoloader or not
   */
  public function register($prepend = false) {
    spl_autoload_register(array($this, 'loadClass'), true, $prepend);
  }

  /**
   * Unregisters this instance as an autoloader.
   */
  public function unregister() {
    spl_autoload_unregister(array($this, 'loadClass'));
  }

  /**
   * Loads the given class or interface.
   *
   * @param  string $class
   *   The name of the class
   * @return bool|null
   *   True if loaded, null otherwise
   */
  public function loadClass($class) {
    if ($file = $this->findFile($class)) {
      include $file;
      return true;
    }
  }
}
