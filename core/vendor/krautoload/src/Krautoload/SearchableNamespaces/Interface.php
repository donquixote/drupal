<?php

namespace Krautoload;

interface SearchableNamespaces_Interface {

  /**
   * Add a namespace to the family.
   *
   * @param string $namespace
   */
  function addNamespace($namespace);

  /**
   * Add namespaces to the family.
   *
   * @param array $namespaces
   */
  function addNamespaces($namespaces);

  /**
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildEmpty();

  /**
   * @param array $namespaces
   *   Namespaces for the new family.
   *
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildFromNamespaces($namespaces);

  /**
   * @param string $suffix
   *   Namespace suffix to append to each namespace.
   *
   * @return SearchableNamespaces_Interface
   *   Newly created namespace family.
   */
  function buildFromSuffix($suffix);

  /**
   * Scan all registered namespaces for classes.
   * Tell the $api object about each class file that is found.
   *
   * @param InjectedAPI_ClassFileVisitor_Interface $api
   * @param array $namespaces
   */
  function apiVisitClassFiles($api, $recursive = FALSE);

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
  function classExistsInNamespaces($class);
}
