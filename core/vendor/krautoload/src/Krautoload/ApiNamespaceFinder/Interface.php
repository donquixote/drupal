<?php

namespace Krautoload;

interface ApiNamespaceFinder_Interface {

  /**
   * @param NamespaceFinderAPI_Interface $api
   * @param string $namespace
   */
  public function apiFindNamespace($api, $namespace);

  /**
   * @param NamespaceFinderAPI_Interface $api
   * @param array $namespaces
   *   If NULL, it will visit all registered namespaces.
   */
  public function apiFindNamespaces($api, $namespaces = NULL);
}
