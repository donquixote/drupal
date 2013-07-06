<?php

namespace Krautoload;

class SearchableNamespaces_Default implements SearchableNamespaces_Interface {

  protected $master;
  protected $namespaces = array();

  /**
   * @param ApiNamespaceFinder_Interface $master
   *   @todo This should be a more universal interface..
   */
  function __construct($master) {
    $this->master = $master;
  }

  /**
   * Add a namespace to the family.
   *
   * @param string $namespace
   */
  function addNamespace($namespace) {
    $this->namespaces[$namespace] = TRUE;
  }

  /**
   * Add namespaces to the family.
   *
   * @param array $namespaces
   */
  function addNamespaces($namespaces) {
    foreach ($namespaces as $namespace) {
      $this->namespaces[$namespace] = TRUE;
    }
  }

  function getNamespaces() {
    return $this->namespaces;
  }

  /**
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildEmpty() {
    return new self($this->master);
  }

  /**
   * @param array $namespaces
   *   Namespaces for the new family.
   *
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildFromNamespaces($namespaces) {
    $family = $this->buildEmpty();
    $family->addNamespaces($namespaces);
    return $family;
  }

  /**
   * @param string $suffix
   *   Namespace suffix to append to each namespace.
   *
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildFromSuffix($suffix) {
    if ('\\' !== $suffix[0]) {
      $suffix = '\\' . $suffix;
    }
    $new = $this->buildEmpty();
    foreach ($this->namespaces as $namespace => $true) {
      $new->addNamespace($namespace . $suffix);
    }
    return $new;
  }

  /**
   * Scan all registered namespaces for classes.
   * Tell the $api object about each class file that is found.
   *
   * @param DiscoveryAPI_Interface $api
   * @param array $namespaces
   */
  function apiScanAll($api, $recursive = FALSE) {
    $namespaceFinderAPI = $recursive ? new NamespaceFinderAPI_ScanRecursive($api) : new NamespaceFinderAPI_ScanNamespace($api);
    $this->master->apiFindNamespaces($namespaceFinderAPI, array_keys($this->namespaces));
  }

  /**
   * Check if the given class is "known", and load it.
   * This will check the following:
   * - Is the class within any of the registered namespaces?
   * - Is there is a file for this class, within the registered directories?
   *   (Include that file, if it exists.)
   * - Is the class defined after file inclusion?
   *
   * The method can return FALSE even if the class is defined
   */
  function classExistsInNamespaces($class) {
    return $this->classIsInNamespaces($class) && $this->classExistsInFinder($class);
  }

  protected function classIsInNamespaces($class) {
    $prefix = $class;
    while (FALSE !== $pos = strrpos($prefix, '\\')) {
      $prefix = substr($prefix, 0, $pos);
      if (isset($this->namespaces[$prefix])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  protected function classExistsInFinder($class) {
    if (Util::classIsDefined($class)) {
      $api = new ClassFinderAPI_FindExistingClass($class);
    }
    else {
      $api = new ClassFinderAPI_LoadClass($class);
    }
    return $this->master->apiFindFile($api, $class);
  }
}
