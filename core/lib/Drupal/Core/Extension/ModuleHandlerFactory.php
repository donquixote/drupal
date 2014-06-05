<?php

/**
 * @file
 * Contains Drupal\Core\Extension\ModuleHandlerFactory.
 */

namespace Drupal\Core\Extension;

use Drupal\Core\Config\StorageInterface;
use Drupal\Core\SystemListing;

/**
 * A factory for ModuleHandler that is responsible for finding both available
 * and enabled modules.
 */
class ModuleHandlerFactory {

  /**
   * A cache to hold module data once it's been built.
   *
   * @var array
   */
  protected $moduleData;

  /**
   * An constructor.
   *
   * @param StorageInterface
   *   A config storage object.
   */
  public function __construct(StorageInterface $config_storage) {
    $this->configStorage = $config_storage;
  }

  /**
   * A static shortcut to encapsulate the instantiation of the factory as well
   * as creating the ModuleHandler.
   *
   * @param StorageInterface
   *   A config storage object.
   * @return ModuleHandler
   *   A module handler.
   */
  public static function get(StorageInterface $config_storage) {
    $factory = new static($config_storage);
    return $factory->create();
  }

  /**
   * Factory method that finds available/enabled modules and instantiates a
   * new ModuleHandler.
   *
   * @return ModuleHandler
   *   An instance of ModuleHandler.
   */
  public function create() {
    $module_info = $this->configStorage->read('system.module');
    $enabled_modules = isset($module_info['enabled']) ? $module_info['enabled'] : array();
    $module_list = $this->findFileNames($enabled_modules);
    return new ModuleHandler($module_list);
  }

  /**
   * Returns the file name for each enabled module.
   *
   * @param array $module_list
   *   An associative array of modules where keys are module names and values
   *   are weight.
   * @return array
   *   An array of module filenames.
   */
  public function findFileNames($module_list) {
    $filenames = array();
    foreach ($module_list as $module => $weight) {
      if ($data = $this->moduleData($module, $module_list)) {
        $filenames[$module] = $data->uri;
      }
    }
    return $filenames;
  }

  /**
   * Returns module data on the filesystem.
   *
   * @param $module
   *   The name of the module.
   * @param array $module_list
   *   An associative array of modules where keys are module names and values
   *   are weight.
   * @return \stdClass|bool
   *   Returns a stdClass object if the module data is found containing at
   *   least an uri property with the module path, for example
   *   core/modules/user/user.module.
   */
  protected function moduleData($module, $module_list) {
    if (!$this->moduleData) {
      $this->buildModuleData($module_list);
    }
    return isset($this->moduleData[$module]) ? $this->moduleData[$module] : FALSE;
  }

  /**
   * Builds moduleData cache by scanning the filesystem.
   *
   * @param array $module_list
   *   An associative array of modules where keys are module names and values
   *   are weight.
   */
  protected function buildModuleData($module_list) {
    // First, find profiles.
    $profiles_scanner = new SystemListing();
    $all_profiles = $profiles_scanner->scan('/^' . DRUPAL_PHP_FUNCTION_PATTERN . '\.profile$/', 'profiles');
    $profiles = array_keys(array_intersect_key($module_list, $all_profiles));
    // If a module is within a profile directory but specifies another
    // profile for testing, it needs to be found in the parent profile.
    if (($parent_profile_config = $this->configStorage->read('simpletest.settings')) && isset($parent_profile_config['parent_profile']) && $parent_profile_config['parent_profile'] != $profiles[0]) {
      // In case both profile directories contain the same extension, the
      // actual profile always has precedence.
      array_unshift($profiles, $parent_profile_config['parent_profile']);
    }
    // Now find modules.
    $modules_scanner = new SystemListing($profiles);
    $this->moduleData = $all_profiles + $modules_scanner->scan('/^' . DRUPAL_PHP_FUNCTION_PATTERN . '\.module$/', 'modules');
  }
}
