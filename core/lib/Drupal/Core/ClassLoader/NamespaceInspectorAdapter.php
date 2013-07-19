<?php

namespace Drupal\Core\ClassLoader;

use Krautoload\NamespaceInspector_Pluggable;
use Krautoload\Adapter_NamespaceInspector_Pluggable as BaseAdapter;

class NamespaceInspectorAdapter extends BaseAdapter implements NamespaceInspectorAdapterInterface {

  /**
   * Construct a namespace inspector adapter with an inspector.
   *
   * @return NamespaceInspectorAdapterInterface
   */
  static function start() {
    $inspector = new NamespaceInspector_Pluggable();
    return new self($inspector);
  }

  /**
   * @inheritdoc
   */
  function addDrupalExtension($extension_name, $extension_dir) {

    // Register the extension's "lib/" directory for PSR-0 class loading.
    $this->addNamespacePSR0('Drupal\\' . $extension_name, DRUPAL_ROOT . '/' . $extension_dir . '/lib');

    // Register the extension's "src/" directory for PSR-4 class loading.
    // @todo
    //   - Make a choice between "src/" and "lib/" for PSR-4 classes.
    //   - Have a transition phase to migrate modules to PSR-4.
    //   - Remove PSR-0 after this transition phase.
    $this->addNamespacePSRX('Drupal\\' . $extension_name, DRUPAL_ROOT . '/' . $extension_dir . '/src');
  }

  /**
   * @inheritdoc
   */
  function addDrupalExtensionTests($extension_name, $extension_dir) {
    $this->addNamespacePSR0('Drupal\\' . $extension_name . '\\Tests', DRUPAL_ROOT . '/' . $extension_dir . '/tests');
    $this->addNamespacePSRX('Drupal\\' . $extension_name . '\\Tests', DRUPAL_ROOT . '/' . $extension_dir . '/tests/src');
  }

  /**
   * @inheritdoc
   */
  function addDrupalExtensionsByFileName(array $extension_filenames) {
    foreach ($extension_filenames as $extension_name => $extension_filename) {
      $this->addDrupalExtension($extension_name, dirname($extension_filename));
    }
  }

  /**
   * @inheritdoc
   */
  function addDrupalCore() {
    $this->addNamespacePSR0('Drupal\Component', DRUPAL_ROOT . '/core/lib');
    $this->addNamespacePSR0('Drupal\Core', DRUPAL_ROOT . '/core/lib');
  }

  /**
   * @inheritdoc
   */
  function addDrupalCoreTests() {
    $this->addNamespacePSR0('Drupal\\Tests', DRUPAL_ROOT . '/core/tests');
  }

  /**
   * @param array $namespaces
   * @return SearchableNamespacesInterface
   * @throws \Exception
   */
  function buildSearchableNamespaces(array $namespaces = array()) {
    $searchable = new SearchableNamespaces($this->finder);
    $searchable->addNamespaces($namespaces);
    return $searchable;
  }
}