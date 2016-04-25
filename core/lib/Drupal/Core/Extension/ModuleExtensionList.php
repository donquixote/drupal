<?php

namespace Drupal\Core\Extension;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a list of available modules.
 */
class ModuleExtensionList extends ExtensionList {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaults = [
    'dependencies' => [],
    'description' => '',
    'package' => 'Other',
    'version' => NULL,
    'php' => DRUPAL_MINIMUM_PHP,
  ];

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The profile list needed by this module list.
   *
   * @var \Drupal\Core\Extension\ExtensionList
   */
  protected $profileList;

  /**
   * Constructs a new ModuleExtensionList instance.
   *
   * @param string $root
   *   The app root.
   * @param string $type
   *   The extension type.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Extension\ExtensionList $profile_list
   *   The site profile listing.
   */
  public function __construct(
    $root,
    $type,
    CacheBackendInterface $cache,
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory,
    ExtensionList $profile_list
  ) {
    parent::__construct($root, $type, $cache, $info_parser, $module_handler);

    $this->configFactory = $config_factory;
    $this->profileList = $profile_list;
  }

  /**
   * {@inheritdoc}
   */
  protected function doScanExtensions() {
    $extensions = parent::doScanExtensions();

    // Find installation profiles.
    $profiles = $this->profileList->listExtensions();

    // Include the installation profile in modules that are loaded.
    if ($profile = drupal_get_profile()) {
      $extensions[$profile] = $profiles[$profile];
      // Installation profile hooks are always executed last.
      $extensions[$profile]->weight = 1000;
    }

    return $extensions;
  }

  /**
   * {@inheritdoc}
   */
  protected function doListExtensions() {
    // Find installation profiles. This needs to happen before performing a
    // module scan as the module scan needs to know what the active profile is.
    $profiles = $this->profileList->listExtensions();
    $profile = drupal_get_profile();
    if ($profile && isset($profiles[$profile])) {
      // Set the profile in the ExtensionDiscovery so we can scan from the right
      // profile directory.
      $this->extensionDiscovery->setProfileDirectories([
        $profile => $profiles[$profile]->getPathname(),
      ]);
    }

    // Find modules.
    $extensions = parent::doListExtensions();
    // It is possible that a module was marked as required by
    // hook_system_info_alter() and modules that it depends on are not required.
    foreach ($extensions as $extension) {
      $this->ensureRequiredDependencies($extension, $extensions);
    }

    if ($profile) {
      // Installation profiles are hidden by default, unless explicitly
      // specified otherwise in the .info.yml file.
      if (!isset($extensions[$profile]->info['hidden'])) {
        $extensions[$profile]->info['hidden'] = TRUE;
      }

      if (isset($extensions[$profile])) {
        // The installation profile is required, if it's a valid module.
        $extensions[$profile]->info['required'] = TRUE;
        // Add a default distribution name if the profile did not provide one.
        // @see install_profile_info()
        // @see drupal_install_profile_distribution_name()
        if (!isset($extensions[$profile]->info['distribution']['name'])) {
          $extensions[$profile]->info['distribution']['name'] = 'Drupal';
        }
      }
    }

    // Add status, weight, and schema version.
    $installed_modules = $this->configFactory->get('core.extension')->get('module') ?: [];
    foreach ($extensions as $name => $module) {
      $module->weight = isset($installed_modules[$name]) ? $installed_modules[$name] : 0;
      $module->status = (int) isset($installed_modules[$name]);
      $module->schema_version = SCHEMA_UNINSTALLED;
    }
    $extensions = $this->moduleHandler->buildModuleDependencies($extensions);

    return $extensions;
  }

  /**
   * Ensures that dependencies of required modules are also required.
   *
   * @param \Drupal\Core\Extension\Extension $module
   *   The module info.
   * @param \Drupal\Core\Extension\Extension[] $modules
   *   The array of all module info.
   */
  protected function ensureRequiredDependencies(Extension $module, array $modules = []) {
    if (!empty($module->info['required'])) {
      foreach ($module->info['dependencies'] as $dependency) {
        $dependency_name = ModuleHandler::parseDependency($dependency)['name'];
        if (!isset($modules[$dependency_name]->info['required'])) {
          $modules[$dependency_name]->info['required'] = TRUE;
          $modules[$dependency_name]->info['explanation'] = $this->t('Dependency of required module @module', array('@module' => $module->info['name']));
          // Ensure any dependencies it has are required.
          $this->ensureRequiredDependencies($modules[$dependency_name], $modules);
        }
      }
    }
  }

}
