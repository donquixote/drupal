<?php

namespace Drupal\Core\ClassLoader;

use Krautoload\Adapter_NamespaceInspector_Interface;

interface NamespaceInspectorAdapterInterface extends ClassLoaderAdapterInterface, Adapter_NamespaceInspector_Interface {

  /**
   * @param array $namespaces
   * @return SearchableNamespacesInterface
   * @throws \Exception
   */
  function buildSearchableNamespaces(array $namespaces = array());
}