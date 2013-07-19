<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Plugin\Discovery\CustomAnnotationClassDiscoveryTest.
 */

namespace Drupal\system\Tests\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;
use Drupal\Core\ClassLoader\NamespaceInspectorAdapter;

/**
 * Tests that a custom annotation class is used.
 *
 * @see \Drupal\plugin_test\Plugin\Annotation\PluginExample
 */
class CustomAnnotationClassDiscoveryTest extends DiscoveryTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Custom annotation class discovery',
      'description' => 'Tests that a custom annotation class is used.',
      'group' => 'Plugin API',
    );
  }

  protected function setUp() {
    parent::setUp();

    $this->expectedDefinitions = array(
      'example_1' => array(
        'id' => 'example_1',
        'custom' => 'John',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\custom_annotation\Example1',
        'provider' => 'plugin_test',
      ),
      'example_2' => array(
        'id' => 'example_2',
        'custom' => 'Paul',
        'class' => 'Drupal\plugin_test\Plugin\plugin_test\custom_annotation\Example2',
        'provider' => 'plugin_test',
      ),
    );

    // Build namespace inspector.
    $adapter = NamespaceInspectorAdapter::start();
    $adapter->addDrupalExtension('plugin_test', 'core/modules/system/tests/modules/plugin_test');
    $adapter->addDrupalCore();

    // Build searchable namespaces.
    $root_namespaces = $adapter->buildSearchableNamespaces(array('Drupal\plugin_test'));

    // Build annotated class discovery.
    $this->discovery = new AnnotatedClassDiscovery($root_namespaces, 'plugin_test\custom_annotation', 'Drupal\plugin_test\Plugin\Annotation\PluginExample');
    $this->discovery->addAnnotationNamespace('Drupal\plugin_test\Plugin\Annotation');
    $this->emptyDiscovery = new AnnotatedClassDiscovery($root_namespaces, 'non_existing_module\non_existing_plugin_type', 'Drupal\plugin_test\Plugin\Annotation\PluginExample');
    $this->emptyDiscovery->addAnnotationNamespace('Drupal\plugin_test\Plugin\Annotation');
  }

}
