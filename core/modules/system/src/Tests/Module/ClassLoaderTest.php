<?php

/**
 * @file
 * Definition of Drupal\system\Tests\Module\ClassLoaderTest.
 */

namespace Drupal\system\Tests\Module;

use Drupal\simpletest\WebTestBase;

/**
 * Tests class loading.
 */
class ClassLoaderTest extends WebTestBase {

  /**
   * The expected result from calling the module-provided class' method.
   */
  protected $expected = 'Drupal\\module_autoload_test\\SomeClass::testMethod() was invoked.';

  public static function getInfo() {
    return array(
      'name' => 'Module class loader',
      'description' => 'Tests class loading for modules.',
      'group' => 'Module',
    );
  }

  /**
   * Tests that module-provided classes can be loaded when a module is enabled.
   *
   * @see \Drupal\module_autoload_test\SomeClass
   */
  function testClassLoading() {
    // Enable the module_test and module_autoload_test modules.
    module_enable(array('module_test', 'module_autoload_test'), FALSE);
    $this->resetAll();
    // Check twice to test an unprimed and primed system_list() cache.
    for ($i=0; $i<2; $i++) {
      $this->drupalGet('module-test/class-loading');
      $this->assertText($this->expected, 'Autoloader loads classes from an enabled module.');
    }
  }

  /**
   * Tests various edge cases with PSR-0.
   *
   * We do each test in a separate request, to make sure that the classes are
   * not already loaded due to a previous test.
   */
  function testPSR0Specialties() {
    // Enable the module_test and module_autoload_test modules.
    module_enable(array('module_test', 'module_autoload_test'), FALSE);
    $this->resetAll();

    // Test that underscores in PSR-0 classes are dealt with correctly.
    $this->drupalGet('module-test/class-loading/psr0-underscores');
    $this->assertText('A class with underscores was found.',
      'Underscores in the class name are replaced with directory separators.');
    $this->assertText('A file with underscores was not included.',
      'No file with underscores in the file name is included.');

    // Test that the PSR-0 class loader does not include the same file twice.
    $this->drupalGet('module-test/class-loading/psr0-multi-include');
    $this->assertText('The application did not crash.',
      'The class loader avoids multiple file inclusion, if two PSR-0 classes are expected to be in the same file.');

    // Test that no classes from the wrong module are included.
    $this->drupalGet('module-test/class-loading/psr0-prefix-clash');
    $this->assertText('A file was not included because it is in the wrong folder.',
      'A PSR-0 class loader will not look in the lib folder of another module.');
  }

  /**
   * Tests PSR-X class loading for modules.
   * PSR-X classes are in the src/ folder of a module.
   */
  function testClassLoadingPSRX() {
    // Enable the module_test and module_autoload_test modules.
    module_enable(array('module_test', 'module_autoload_test'), FALSE);
    $this->resetAll();

    // Check that PSR-X classes are found.
    $this->drupalGet('module-test/class-loading/psrx');
    $this->assertText('SomeClassPSRX was found.',
      'Classes are found by the PSR-X class loader.');
    $this->assertText('ClassWith_Underscore_PSRX was found.',
      'Classes with underscores are found by the PSR-X class loader.');
    $this->assertText('Class in sub-namespace was found.',
      'Classes in sub-namespaces are found by the PSR-X class loader.');
  }

  /**
   * Tests that module-provided classes can't be loaded from disabled modules.
   *
   * @see \Drupal\module_autoload_test\SomeClass
   */
  function testClassLoadingDisabledModules() {
    // Ensure that module_autoload_test is disabled.
    module_disable(array('module_autoload_test'), FALSE);
    $this->resetAll();
    // Check twice to test an unprimed and primed system_list() cache.
    for ($i=0; $i<2; $i++) {
      $this->drupalGet('module-test/class-loading');
      $this->assertNoText($this->expected, 'Autoloader does not load classes from a disabled module.');
    }
  }
}
