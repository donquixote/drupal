<?php

/**
 * @file
 * Contains \Drupal\Core\Condition\ConditionManager.
 */

namespace Drupal\Core\Condition;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Executable\ExecutableInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * A plugin manager for condition plugins.
 */
class ConditionManager extends DefaultPluginManager implements ExecutableManagerInterface {

  /**
   * Constructs a ConditionManager object.
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    $this->alterInfo($module_handler, 'condition_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'condition');

    parent::__construct($root_namespaces, 'Condition', array('Drupal\Core\Condition\Annotation'), 'Drupal\Core\Condition\Annotation\Condition');
  }

  /**
   * Override of Drupal\Component\Plugin\PluginManagerBase::createInstance().
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    $plugin = $this->factory->createInstance($plugin_id, $configuration);
    return $plugin->setExecutableManager($this);
  }

  /**
   * Implements Drupal\Core\Executable\ExecutableManagerInterface::execute().
   */
  public function execute(ExecutableInterface $condition) {
    $result = $condition->evaluate();
    return $condition->isNegated() ? !$result : $result;
  }

}
