<?php

namespace Krautoload;

class SearchableNamespaces_Default implements SearchableNamespaces_Interface {

  protected $master;
  protected $discovery;

  /**
   * @param ApiClassDiscovery_Interface $master
   *   @todo This should be a more universal interface..
   * @param ApiClassDiscovery_Interface $discovery
   *   @todo This should be a more universal interface..
   */
  function __construct($master, $discovery) {
    $this->master = $master;
    $this->discovery = $discovery;
  }

  /**
   * Add a namespace to the family.
   *
   * @param string $namespace
   */
  function addNamespace($namespace) {
    $this->addNamespaces(array($namespace));
  }

  /**
   * Add namespaces to the family.
   *
   * @param array $namespaces
   */
  function addNamespaces($namespaces) {
    $api = new NamespaceFinderAPI_ClassFinderEnhancer($this->discovery);
    $this->master->apiFindNamespaces($api, $namespaces);
  }

  /**
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildEmpty() {
    return new self($this->master, new ApiClassDiscovery_Pluggable());
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
    $new = new ApiClassDiscovery_Pluggable();
    $api = new NamespaceFinderAPI_SuffixBuilder($new, $suffix);
    $this->discovery->apiFindNamespaces($api);
    return new self($this->master, $new);
  }

  /**
   * Scan all registered namespaces for classes.
   * Tell the $api object about each class file that is found.
   *
   * @param DiscoveryAPI_Interface $api
   * @param array $namespaces
   */
  function apiScanAll($api, $recursive = FALSE) {
    return $this->discovery->apiScanAll($api, $recursive);
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
    if (Util::classIsDefined($class)) {
      $api = new ClassFinderAPI_FindExistingClass($class);
    }
    else {
      $api = new ClassFinderAPI_LoadClass($class);
    }
    return $this->discovery->apiFindFile($api, $class);
  }
}
