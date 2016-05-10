<?php

namespace Drupal\Core\Extension\List_\Processor;

use Drupal\Core\Extension\List_\ExtensionListUtil;

class ExtensionListProcessor_Dependencies implements ExtensionListProcessorInterface {

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {
    ExtensionListUtil::buildDependencies($extensions);
  }
}
