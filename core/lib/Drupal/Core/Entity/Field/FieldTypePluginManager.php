<?php

/**
 * @file
 *
 * Contains \Drupal\Core\Entity\Field\FieldTypePluginManager.
 */

namespace Drupal\Core\Entity\Field;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\field\Plugin\Type\FieldType\LegacyFieldTypeDiscoveryDecorator;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Plugin manager for 'field type' plugins.
 */
class FieldTypePluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  protected $defaults = array(
    'settings' => array(),
    'instance_settings' => array(),
    'list_class' => '\Drupal\field\Plugin\Type\FieldType\ConfigField',
  );

  /**
   * Constructs the FieldTypePluginManager object
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($root_namespaces, 'field\field_type', array('Drupal\Core\Entity\Annotation'), 'Drupal\Core\Entity\Annotation\FieldType');
    $this->alterInfo($module_handler, 'field_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'field_types');

    // @todo Remove once all core field types have been converted (see
    // http://drupal.org/node/2014671).
    $this->discovery = new LegacyFieldTypeDiscoveryDecorator($this->discovery, $module_handler);
  }

}
