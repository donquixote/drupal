<?php

namespace Krautoload;

class NamespaceFinderAPI_SuffixBuilder implements NamespaceFinderAPI_Interface {

  protected $finder;
  protected $pathSuffix;
  protected $namespaceLogicalPath;

  function __construct($finder, $namespaceSuffix) {
    if ('' !== $namespaceSuffix && '\\' !== substr($namespaceSuffix, -1)) {
      $namespaceSuffix = $namespaceSuffix . '\\';
    }
    $this->pathSuffix = str_replace('\\', DIRECTORY_SEPARATOR, $namespaceSuffix);
  }

  function setNamespace($namespace) {
    $this->namespaceLogicalPath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
  }

  function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    $newLogicalBasePath = $this->namespaceLogicalPath . $this->pathSuffix;
    $newBaseDir = $baseDir . $relativePath . $this->pathSuffix;
    // @todo: Let the plugin have a say.
    if (is_dir($newBaseDir) || TRUE) {
      $this->finder->registerNamespacePathPlugin($newLogicalBasePath, $newBaseDir, $plugin);
    }
  }
}
