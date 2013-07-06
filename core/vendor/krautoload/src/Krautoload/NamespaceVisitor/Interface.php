<?php

namespace Krautoload;

interface NamespaceVisitor_Interface {

  /**
   * @param InjectedAPI_NamespaceVisitor_Interface $api
   * @param string $namespace
   */
  public function apiVisitNamespace($api, $namespace);

  /**
   * @param InjectedAPI_NamespaceVisitor_Interface $api
   * @param array $namespaces
   *   If NULL, it will visit all registered namespaces.
   */
  public function apiVisitNamespaces($api, $namespaces);
}
