<?php
/**
 * @file
 * Contains \Drupal\Tests\Core\Fied\FieldDefinitionTestBase.
 */

namespace Drupal\Tests\Core\Field;

use Drupal\Component\Utility\String;
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

    list($module_name, $module_dir) = $this->getModuleAndPath();

    $namespaces = new \ArrayObject(
      array(
        "Drupal\\$module_name" => array(
          // Suppport both PSR-0 and PSR-4 directory layouts.
          $module_dir . '/src',
          // @todo Remove this when PSR-0 support ends.
          // @see https://drupal.org/node/2247287
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
   * Returns the module name and the module directory, for the module that
   * contains the plugin to be tested.
   *
   * drupal_get_path() cannot be used here, because it is not available in
   * Drupal PHPUnit tests.
   *
   * This is a default implementation, where the module name and module
   * directory are determined from the test class and test class file via
   * reflection, but it can be overridden.
   *
   * An exception will be thrown for classes where the default implementation
   * does not work, e.g. if the class is not in a module namespace.
   *
   * @return string[]
   *   Numeric array containing the module name and the module directory, e.g.
   *   array('path', DRUPAL_CORE . 'core/modules/path')
   *
   * @throws \Exception
   */
  protected function getModuleAndPath() {
    $class = get_class($this);
    return $this->classExtractModuleAndPath($class);
  }

  /**
   * Determines the name and path of the Drupal module that contains a class.
   *
   * If not possible, an exception will be thrown.
   *
   * Determines if a given class is part of a Drupal module, and determines the
   * path to the *.info.yml file, if possible.
   *
   * @todo Put this in a globally accessible place.
   * @todo Make this work for non-test classes.
   * @todo Use dedicated exception classes.
   *
   * @param string $class
   *   The fully-qualified class name, e.g.
   *   "Drupal\\path\\Tests\\PathFieldDefinitionTest".
   *
   * @return string[]
   *   Numeric array containing the module name and the module directory, e.g.
   *   array('path', DRUPAL_CORE . 'core/modules/path')
   *
   * @throws \Exception
   */
  private function classExtractModuleAndPath($class) {

    if (!preg_match("/Drupal\\\\(.+)\\\\Tests\\\\(.+)$/", $class, $m)) {
      throw new \Exception("Not a module test class: '$class'.");
    }

    list(, $module_name, $relative_class_name) = $m;

    $relative_path = str_replace('\\', '/', $relative_class_name) . '.php';

    $suffixes = array(
      // PSR-4 location for PHPUnit tests.
      '/tests/src/' . $relative_path,
      // PSR-0 location for PHPUnit tests.
      // @todo Remove when PSR-0 support has ended.
      // @see https://drupal.org/node/2247287
      "/tests/Drupal/$module_name/Tests/" . $relative_path,
    );

    $class_file = (new \ReflectionClass($class))->getFileName();
    if ($class_file === FALSE) {
      throw new \Exception("Not in a class file: '$class'.");
    }

    // Normalize the directory separator to '/'.
    $class_file = str_replace(DIRECTORY_SEPARATOR, '/', $class_file);

    foreach ($suffixes as $suffix) {
      $dir = String::removeSuffix($class_file, $suffix);
      if (FALSE !== $dir) {
        if (is_file($dir . '/' . $module_name . '.info.yml')) {
          return array($module_name, $dir);
        }
      }
    }

    throw new \Exception("No module info file found.");
  }

}
