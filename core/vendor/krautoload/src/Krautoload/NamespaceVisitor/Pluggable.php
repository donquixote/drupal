<?php

namespace Krautoload;

class NamespaceVisitor_Pluggable extends ClassFinder_Pluggable implements NamespaceVisitor_Interface {

  /**
   * @param InjectedAPI_NamespaceVisitor_Interface $api
   * @param string $namespace
   */
  public function apiVisitNamespace($api, $namespace) {

    // Discard initial namespace separator.
    if ('\\' === $namespace[0]) {
      $namespace = substr($namespace, 1);
    }
    
    $logicalPath = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    $logicalBasePath = $logicalPath;
    $relativePath = '';

    $api->setNamespace($namespace);

    while (TRUE) {

      // Check any plugin registered for this fragment.
      if (!empty($this->namespaceMap[$logicalBasePath])) {
        foreach ($this->namespaceMap[$logicalBasePath] as $baseDir => $plugin) {
          $api->namespaceDirectoryPlugin($baseDir, $relativePath, $plugin);
        }
      }

      // Continue with parent fragment.
      if ('' === $logicalBasePath) {
        break;
      }
      elseif (DIRECTORY_SEPARATOR === $logicalBasePath) {
        // This happens if a class begins with an underscore.
        $logicalBasePath = '';
        $relativePath = $logicalPath;
      }
      elseif (FALSE !== $pos = strrpos($logicalBasePath, DIRECTORY_SEPARATOR, -2)) {
        $logicalBasePath = substr($logicalBasePath, 0, $pos + 1);
        $relativePath = substr($logicalPath, $pos + 1);
      }
      else {
        $logicalBasePath = '';
        $relativePath = $logicalPath;
      }
    }
  }

  /**
   * @param InjectedAPI_NamespaceVisitor_Interface $api
   * @param array $namespaces
   */
  public function apiVisitNamespaces($api, $namespaces) {
    foreach ($namespaces as $namespace) {
      $this->apiVisitNamespace($api, $namespace);
    }
  }

  public function apiVisitBaseNamespaces($api) {
    foreach ($this->namespaceMap as $logicalBasePath => $plugins) {
      $namespace = str_replace(DIRECTORY_SEPARATOR, '\\', substr($logicalBasePath, 0, -1));
      $api->setNamespace($namespace);
      foreach ($plugins as $baseDir => $plugin) {
        $api->namespaceDirectoryPlugin($baseDir, '', $plugin);
      }
    }
  }
}
