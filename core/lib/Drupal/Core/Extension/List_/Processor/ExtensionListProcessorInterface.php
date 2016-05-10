<?php

namespace Drupal\Core\Extension\List_\Processor;

interface ExtensionListProcessorInterface {

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions);

}
