<?php

namespace Krautoload;

class NamespaceFinderAPI_ClassFinderEnhancer implements NamespaceFinderAPI_Interface {

  protected $namespaceLogicalPath;
  protected $finder;

  function __construct($finder) {
    $this->finder = $finder;
  }

  function setNamespace($namespace) {
    $this->namespaceLogicalPath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
  }

  function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $newBaseDir = $baseDir . $relativePath;
    // @todo Let the plugin have a say.
    if (is_dir($newBaseDir)) {
      $this->finder->registerNamespacePathPlugin($this->namespaceLogicalPath, $newBaseDir, $plugin);
    }
  }
}
