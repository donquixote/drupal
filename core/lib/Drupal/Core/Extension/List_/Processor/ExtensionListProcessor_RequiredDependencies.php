<?php

namespace Drupal\Core\Extension\List_\Processor;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

class ExtensionListProcessor_RequiredDependencies implements ExtensionListProcessorInterface {

  use StringTranslationTrait;

  /**
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   */
  public function __construct(TranslationInterface $stringTranslation) {
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {
    foreach ($extensions as $extension) {
      if (!empty($extension->info['required'])) {
        $this->ensureRequiredDependencies($extension, $extensions);
      }
    }
  }

  /**
   * Ensures that dependencies of required modules are also required.
   *
   * @param \Drupal\Core\Extension\Extension $required_extension
   *   The extension object.
   * @param \Drupal\Core\Extension\Extension[] $extensions
   *   The array of all objects of this type.
   */
  protected function ensureRequiredDependencies(Extension $required_extension, array $extensions = []) {
    foreach ($required_extension->info['dependencies'] as $dependency) {
      $dependency_name = ModuleHandler::parseDependency($dependency)['name'];
      if (!array_key_exists($dependency_name, $extensions)) {
        // Required extension is not available, so we cannot do anything.
        continue;
      }
      $dependency_extension = $extensions[$dependency_name];
      if (isset($dependency_extension->info['required'])) {
        // Already processed, or already required.
        continue;
      }
      $dependency_extension->info['required'] = TRUE;
      // @todo Use an injected (lazy/proxy) translation service.
      $dependency_extension->info['explanation'] = $this->t('Dependency of required module @module', array('@module' => $required_extension->info['name']));

      // Ensure any dependencies it has are required.
      $this->ensureRequiredDependencies($dependency_extension, $extensions);
    }
  }
}
