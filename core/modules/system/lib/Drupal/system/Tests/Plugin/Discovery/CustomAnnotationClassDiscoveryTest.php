<?php

/**
 * @file
 * Contains \Drupal\system\Tests\Plugin\Discovery\CustomAnnotationClassDiscoveryTest.
 */

namespace Drupal\system\Tests\Plugin\Discovery;

use Drupal\Core\Plugin\Discovery\AnnotatedClassDiscovery;

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

    // Build namespace finder.
    $finder = new \Krautoload\NamespaceVisitor_Pluggable();
    $registrationHub = new \Krautoload\RegistrationHub($finder);
    $registrationHub->addNamespacePSR0('Drupal\plugin_test', DRUPAL_ROOT . '/core/modules/system/tests/modules/plugin_test/lib');
    $registrationHub->addNamespacePSR0('Drupal\Component', DRUPAL_ROOT . '/core/lib');
    $registrationHub->addNamespacePSR0('Drupal\Core', DRUPAL_ROOT . '/core/lib');

    // Build searchable namespaces.
    $root_namespaces = $registrationHub->buildSearchableNamespaces(array('Drupal\plugin_test'));
    $annotation_namespaces = $registrationHub->buildSearchableNamespaces(array('Drupal\plugin_test\Plugin\Annotation'));

    // Build annotated class discovery.
    $this->discovery = new AnnotatedClassDiscovery('plugin_test/custom_annotation', $root_namespaces, $annotation_namespaces, 'Drupal\plugin_test\Plugin\Annotation\PluginExample');
    $this->emptyDiscovery = new AnnotatedClassDiscovery('non_existing_module/non_existing_plugin_type', $root_namespaces, $annotation_namespaces, 'Drupal\plugin_test\Plugin\Annotation\PluginExample');
  }

}
