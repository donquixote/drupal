<?php

/**
 * @file
 * Contains \Drupal\edit\Plugin\InPlaceEditorManager.
 */

namespace Drupal\edit\Plugin;

use Drupal\Component\Plugin\PluginManagerBase;
use Drupal\Component\Plugin\Discovery\ProcessDecorator;
use Drupal\Component\Plugin\Factory\DefaultFactory;
use Drupal\Core\Plugin\Discovery\AlterDecorator;
use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\Plugin\Discovery\CacheDecorator;
use Drupal\Core\ClassLoader\SearchableNamespacesInterface;

/**
 * Editor manager.
 *
 * The form editor must always be available.
 */
class InPlaceEditorManager extends PluginManagerBase {

  /**
   * Overrides \Drupal\Component\Plugin\PluginManagerBase::__construct().
   *
   * @param SearchableNamespacesInterface $root_namespaces
   *   Searchable namespaces for enabled extensions and core.
   *   This will be used to build the plugin namespaces by adding the suffix.
   *   E.g. the root namespace for a module is Drupal\$module.
   */
  public function __construct(SearchableNamespacesInterface $root_namespaces) {
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'InPlaceEditor', 'Drupal\edit\Annotation\InPlaceEditor');
    $this->discovery->addAnnotationNamespace('Drupal\edit\Annotation');
    $this->discovery = new AlterDecorator($this->discovery, 'edit_editor');
    $this->discovery = new CacheDecorator($this->discovery, 'edit:editor');
    $this->factory = new DefaultFactory($this->discovery);
  }

}
