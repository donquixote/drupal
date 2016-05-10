<?php

namespace Drupal\Core\Extension\List_\Processor;

class ExtensionListProcessor_InstalledWeightsStatic implements ExtensionListProcessorInterface {

  /**
   * @var int[]
   *   Format: $[$extension_name] = $weight
   */
  private $weights;

  /**
   * @param int[] $weights
   *   Format: $[$extension_name] = $weight
   */
  function __construct(array $weights) {
    $this->weights = $weights;
  }

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {
    foreach ($extensions as $name => $extension) {
      if (array_key_exists($name, $this->weights)) {
        $extension->weight = $this->weights[$name];
        $extension->status = 1;
      }
      else {
        $extension->weight = 0;
        $extension->status = 0;
      }
      // @todo Is this ever changed or used, anywhere?
      // SCHEMA_UNINSTALLED might not be defined yet.
      $extension->schema_version = -1;
    }
  }
}
