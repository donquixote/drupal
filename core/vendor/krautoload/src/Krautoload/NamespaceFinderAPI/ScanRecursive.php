<?php

namespace Krautoload;

class NamespaceFinderAPI_ScanRecursive extends NamespaceFinderAPI_ScanAbstract {

  public function namespaceDirectoryPlugin($baseDir, $relativePath, $plugin) {
    if (is_array($baseDir)) {
      throw new \Exception("Base dir must not be array.");
    }
    if (is_array($relativePath)) {
      throw new \Exception("Relative path must not be array.");
    }
    $plugin->pluginScanRecursive($this->api, $baseDir, $relativePath);
  }
}
