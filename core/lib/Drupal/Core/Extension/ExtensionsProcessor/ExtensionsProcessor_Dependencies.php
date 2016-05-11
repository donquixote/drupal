<?php

namespace Drupal\Core\Extension\ExtensionsProcessor;

use Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameUtil;

class ExtensionsProcessor_Dependencies implements ExtensionsProcessorInterface {

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {
    ExtensionsByNameUtil::buildDependencies($extensions);
  }
}
