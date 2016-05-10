<?php

namespace Drupal\Core\Extension\List_\Processor;

use Drupal\Core\Extension\ModuleHandlerInterface;

class ExtensionListProcessor_SystemInfoAlter implements ExtensionListProcessorInterface {

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * @var string
   */
  private $type;

  /**
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param string $type
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, $type) {
    $this->moduleHandler = $moduleHandler;
    $this->type = $type;
  }

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {
    foreach ($extensions as $extension) {
      $this->moduleHandler->alter('system_info', $extension->info, $extension, $this->type);
    }
  }
}
