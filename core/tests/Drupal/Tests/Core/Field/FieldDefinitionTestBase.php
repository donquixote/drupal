<?php
/**
 * @file
 * Contains \Drupal\Tests\Core\Fied\FieldDefinitionTestBase.
 */

namespace Drupal\Tests\Core\Field;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Field\FieldDefinition;
use Drupal\Core\Field\FieldTypePluginManager;
use Drupal\Core\Language\Language;
use Drupal\Tests\UnitTestCase;

/**
 * Provides a test base class for testing field definitions.
 */
abstract class FieldDefinitionTestBase extends UnitTestCase {

  /**
   * The field definition used in this test.
   *
   * @var \Drupal\Core\Field\FieldDefinition
   */
  protected $definition;

  /**
   * {@inheritdoc}
   */
  public function setUp() {

    $module_info_file = $this->getModuleInfoFilePath();
    if (!preg_match('#^(.*)/([^/]+)\.info\.yml$#', $module_info_file, $m)) {
      throw new \Exception("Unexpected module info file.");
    }

    list(, $module_dir, $module_name) = $m;

    $namespaces = new \ArrayObject(
      array(
        "Drupal\\$module_name" => array(
          $module_dir . '/src',
          $module_dir . '/lib/Drupal/' . $module_name,
        ),
      )
    );

    $language_manager = $this->getMock('Drupal\Core\Language\LanguageManagerInterface');
    $language_manager->expects($this->once())
      ->method('getCurrentLanguage')
      ->will($this->returnValue(new Language(array('id' => 'en'))));
    $module_handler = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $module_handler->expects($this->once())
      ->method('moduleExists')
      ->with($module_name)
      ->will($this->returnValue(TRUE));
    $plugin_manager = new FieldTypePluginManager(
      $namespaces,
      $this->getMock('Drupal\Core\Cache\CacheBackendInterface'),
      $language_manager,
      $module_handler
    );

    $container = new ContainerBuilder();
    $container->set('plugin.manager.field.field_type', $plugin_manager);
    // The 'string_translation' service is used by the @Translation annotation.
    $container->set('string_translation', $this->getStringTranslationStub());
    \Drupal::setContainer($container);

    $this->definition = FieldDefinition::create($this->getPluginId());
  }

  /**
   * Returns the plugin ID of the tested field type.
   *
   * @return string
   *   The plugin ID.
   */
  abstract protected function getPluginId();

  /**
   * Returns the path to the *.info.yml file of the module where the test should
   * look for plugins.
   *
   * This will be used to determine both the module name and the module
   * directory.
   *
   * @return string
   *   The path to the MODULE.info.yml file, e.g.
   *   DRUPAL_ROOT . "/core/modules/path/path.info.yml".
   */
  abstract protected function getModuleInfoFilePath();

}
