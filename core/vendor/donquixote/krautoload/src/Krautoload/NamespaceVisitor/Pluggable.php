<?php

namespace Krautoload;

class NamespaceVisitor_Pluggable extends ClassFinder_Pluggable implements NamespaceVisitor_Interface {

  /**
   * Find namespace path plugins that are registered for a parent namespace
   * of the given namespace, and tell the $api object about it.
   *
   * @param InjectedAPI_NamespaceVisitor_Interface $api
   *   Object that will be informed for every matching plugin/path/namespace.
   * @param string $namespace
   *   The namespace to look for.
   */
  public function apiFindNamespace($api, $namespace) {

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
}
