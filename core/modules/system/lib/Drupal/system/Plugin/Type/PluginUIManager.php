<?php

/**
 * @file
 * Contains \Drupal\system\Plugin\Type\PluginUIManager.
 */

namespace Drupal\system\Plugin\Type;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Manages discovery and instantiation of Plugin UI plugins.
 *
 * @todo This class needs @see references and/or more documentation.
 */
class PluginUIManager extends PluginManagerBase {

  /**
   * Constructs a \Drupal\system\Plugin\Type\PluginUIManager object.
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'PluginUI');
    $this->discovery = new DerivativeDiscoveryDecorator($this->discovery);
    $this->discovery = new AlterDecorator($this->discovery, 'plugin_ui');
    $this->discovery = new CacheDecorator($this->discovery, 'plugin_ui');
    $this->factory = new DefaultFactory($this->discovery);
  }

  /**
   * Overrides \Drupal\Component\Plugin\PluginManagerBase::processDefinition().
   */
  public function processDefinition(&$definition, $plugin_id) {
    $definition += array(
      'default_task' => TRUE,
      'task_title' => t('View'),
      'task_suffix' => 'view',
      'access_callback' => 'user_access',
    );
  }

}
