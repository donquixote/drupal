<?php

namespace Krautoload;

class SearchableNamespaces_ArrayObject extends SearchableNamespaces_ArrayObject {

  protected $finder;
  protected $namespaces;

  /**
   * @param NamespaceVisitor_Interface $master
   *   @todo This should be a more universal interface..
   */
  function __construct($master, $namespaces) {
    $this->finder = $master;
    $this->namespaces = $namespaces;
  }

  /**
   * Get namespaces.
   *
   * @param array $namespaces
   */
  function getNamespaces() {
    return $this->namespaces->getArrayCopy();
  }

  /**
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildEmpty() {
    return new self($this->finder);
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
    foreach ($this->namespaces as $namespace) {
      $new->addNamespace($namespace . $suffix);
    }
    return $new;
  }

  /**
   * Scan all registered namespaces for classes.
   * Tell the $api object about each class file that is found.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param boolean $recursive
   */
  function apiVisitClassFiles($api, $recursive = FALSE) {
    $namespaceFinderAPI = $recursive ? new InjectedAPI_NamespaceVisitor_ScanRecursive($api) : new InjectedAPI_NamespaceVisitor_ScanNamespace($api);
    $this->finder->apiVisitNamespaces($namespaceFinderAPI, array_keys($this->namespaces));
  }

  /**
   * Scan all registered namespaces for class files, include each file, and
   * return all classes that actually exist (but no interfaces).
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param boolean $recursive
   *
   * @return array
   *   Collected class names.
   */
  function discoverExistingClasses($recursive = FALSE) {
    $api = new InjectedAPI_ClassFileVisitor_CollectExistingClasses();
    $this->apiVisitClassFiles($api, $recursive);
    return $api->getCollectedClasses();
  }

  /**
   * Scan all registered namespaces for class files, and return all names that
   * may be defined as a class or interface within these namespaces.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param boolean $recursive
   *
   * @return array
   *   Collected class names.
   */
  function discoverCandidateClasses($recursive = FALSE) {
    $api = new InjectedAPI_ClassFileVisitor_CollectCandidateClasses();
    $this->apiVisitClassFiles($api, $recursive);
    return $api->getCollectedClasses();
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
      $api = new InjectedAPI_ClassFinder_FindExistingClass($class);
    }
    else {
      $api = new InjectedAPI_ClassFinder_LoadClass($class);
    }
    return $this->finder->apiFindFile($api, $class);
  }
}
