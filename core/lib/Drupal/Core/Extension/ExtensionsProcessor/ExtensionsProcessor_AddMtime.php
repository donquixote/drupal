<?php

namespace Drupal\Core\Extension\ExtensionsProcessor;

class ExtensionsProcessor_AddMtime implements ExtensionsProcessorInterface {

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {
    foreach ($extensions as $extension) {
      $extension->info['mtime'] = filemtime($extension->getPathname());
    }
  }
}
