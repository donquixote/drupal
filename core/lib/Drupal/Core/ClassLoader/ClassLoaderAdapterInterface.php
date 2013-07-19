<?php

namespace Drupal\Core\ClassLoader;

use Krautoload\Adapter_ClassLoader_Interface;

interface ClassLoaderAdapterInterface extends Adapter_ClassLoader_Interface {

  /**
   * Add namespace directories for a Drupal extension.
   *
   * @param string $extension_name
   * @param string $extension_dir
   */
  function addDrupalExtension($extension_name, $extension_dir);

  /**
   * Add namespace directories for a Drupal extension Tests namespace.
   *
   * @param string $extension_name
   * @param string $extension_dir
   */
  function addDrupalExtensionTests($extension_name, $extension_dir);

  /**
   * @param array $extension_filenames
   */
  function addDrupalExtensionsByFileName(array $extension_filenames);

  /**
   * Add namespace directories for Drupal core.
   */
  function addDrupalCore();

  /**
   * Add Tests namespace directory for Drupal core.
   */
  function addDrupalCoreTests();
}