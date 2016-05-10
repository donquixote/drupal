<?php

namespace Drupal\Core\Extension\List_\Processor;

class ExtensionListProcessor_AddMtime implements ExtensionListProcessorInterface {

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {
    foreach ($extensions as $extension) {
      $extension->info['mtime'] = filemtime($extension->getPathname());
    }
  }
}
