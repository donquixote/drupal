<?php

namespace Drupal\Core\Extension\ExtensionsProcessor;

interface ExtensionsProcessorInterface {

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions);

}
