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
   * This defaults to the module that contains the test, but can be overridden.
   *
   * @return string
   *   The path to the MODULE.info.yml file, e.g.
   *   DRUPAL_ROOT . "/core/modules/path/path.info.yml".
   *
   * @throws \Exception
   */
  protected function getModuleInfoFilePath() {

    $class = get_class($this);
    $module_info_file = $this->classExtractModuleInfoFilePath($class);
    if ($module_info_file === FALSE) {
      throw new \Exception("No module info file found for class '$class'.");
    }
    return $module_info_file;
  }

  /**
   * Determines if a given class is part of a Drupal module, and determines the
   * path to the *.info.yml file, if possible.
   *
   * @todo Put this in a globally accessible place.
   *
   * @param string $class
   *   The fully-qualified class name, e.g.
   *   "Drupal\\path\\Tests\\PathFieldDefinitionTest".
   *
   * @return string|false
   *   The path to the module info file, or FALSE if it could not be determined.
   */
  private function classExtractModuleInfoFilePath($class) {

    if (!preg_match("/Drupal\\\\(.+)\\\\Tests\\\\(.+)$/", $class, $m)) {
      return FALSE;
    }

    list(, $module_name, $relative_class_name) = $m;

    $relative_path = str_replace('\\', '/', $relative_class_name) . '.php';

    $suffixes = array(
      // PSR-4 location for PHPUnit tests.
      '/tests/src/' . $relative_path,
      // PSR-0 location for PHPUnit tests.
      // @todo Remove when PSR-0 support has ended.
      "/tests/Drupal/$module_name/Tests/" . $relative_path,
    );

    $class_file = (new \ReflectionClass($class))->getFileName();
    if ($class_file === FALSE) {
      return FALSE;
    }

    // Normalize the directory separator to '/'.
    $class_file = str_replace(DIRECTORY_SEPARATOR, '/', $class_file);

    foreach ($suffixes as $suffix) {
      $dir = $this->stringRemoveSuffix($class_file, $suffix);
      if (FALSE !== $dir) {
        $module_info_file = $dir . '/' . $module_name . '.info.yml';
        if (is_file($module_info_file)) {
          return $module_info_file;
        }
      }
    }

    return FALSE;
  }

  /**
   * Removes a suffix from a string, if possible.
   *
   * @todo Put this in a globally accessible place.
   *
   * @param string $string
   *   A string to test.
   * @param string $suffix
   *   A string that could be a suffix of $string.
   *
   * @return bool|string
   *   The $prefix, so that $prefix . $suffix === $string, or
   *   FALSE, if $string does not end with $suffix.
   */
  private function stringRemoveSuffix($string, $suffix) {
    if (FALSE !== $pos = strrpos($string, $suffix)) {
      if ($pos + strlen($suffix) === strlen($string)) {
        // $string ends with $suffix, so return the beginning of $string.
        return substr($string, 0, $pos);
      }
    }
    // $string does not end with $suffix.
    return FALSE;
  }

}
