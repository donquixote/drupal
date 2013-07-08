<?php

/**
 * @file
 * Contains \Drupal\entity_reference\Plugin\Type\SelectionPluginManager.
 */

namespace Drupal\entity_reference\Plugin\Type;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\Factory\ReflectionFactory;
use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\entity_reference\Plugin\Type\Selection\SelectionBroken;
use Krautoload\SearchableNamespaces_Interface as SearchableNamespacesInterface;

/**
 * Plugin type manager for the Entity Reference Selection plugin.
 */
class SelectionPluginManager extends PluginManagerBase {

  /**
   * Constructs a SelectionPluginManager object.
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'entity_reference\selection');
    $this->discovery = new AlterDecorator($this->discovery, 'entity_reference_selection');
    $this->discovery = new CacheDecorator($this->discovery, 'entity_reference_selection');
    $this->factory = new ReflectionFactory($this);
  }

  /**
   * Overrides \Drupal\Component\Plugin\PluginManagerBase::createInstance().
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    // We want to provide a broken handler class whenever a class is not found.
    try {
      return parent::createInstance($plugin_id, $configuration);
    }
    catch (PluginException $e) {
      return new SelectionBroken($configuration['field_definition']);
    }
  }

  /**
   * Overrides \Drupal\Component\Plugin\PluginManagerBase::getInstance().
   */
  public function getInstance(array $options) {
    $selection_handler = $options['field_definition']->getFieldSetting('handler');
    $target_entity_type = $options['field_definition']->getFieldSetting('target_type');

    // Get all available selection plugins for this entity type.
    $selection_handler_groups = $this->getSelectionGroups($target_entity_type);

    // Sort the selection plugins by weight and select the best match.
    uasort($selection_handler_groups[$selection_handler], 'drupal_sort_weight');
    end($selection_handler_groups[$selection_handler]);
    $plugin_id = key($selection_handler_groups[$selection_handler]);

    return $this->createInstance($plugin_id, $options);
  }

  /**
   * Returns a list of selection plugins that can reference a specific entity
   * type.
   *
   * @param string $entity_type
   *   A Drupal entity type.
   *
   * @return array
   *   An array of selection plugins grouped by selection group.
   */
  public function getSelectionGroups($entity_type) {
    $plugins = array();

    foreach ($this->getDefinitions() as $plugin_id => $plugin) {
      if (!isset($plugin['entity_types']) || in_array($entity_type, $plugin['entity_types'])) {
        $plugins[$plugin['group']][$plugin_id] = $plugin;
      }
    }

    return $plugins;
  }
}
