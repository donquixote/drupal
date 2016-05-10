<?php

namespace Drupal\Core\Extension\List_\Processor;

use Drupal\Core\Config\ConfigFactoryInterface;

class ExtensionListProcessor_InstalledWeightsFromConfigFactory implements ExtensionListProcessorInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @var string
   */
  private $type;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param string $type
   */
  function __construct(ConfigFactoryInterface $configFactory, $type) {
    $this->configFactory = $configFactory;
    $this->type = $type;
  }

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {

    // Add status, weight, and schema version.
    $installed_modules = $this->configFactory->get('core.extension')->get($this->type) ?: [];
    foreach ($extensions as $name => $extension) {
      if (isset($installed_modules[$name])) {
        $extension->weight = $installed_modules[$name];
        $extension->status = 1;
      }
      else {
        $extension->weight = 0;
        $extension->status = 0;
      }
      // @todo Is this ever changed, anywhere?
      // @todo Is this ever changed or used, anywhere?
      $extension->schema_version = SCHEMA_UNINSTALLED;
    }
  }
}
