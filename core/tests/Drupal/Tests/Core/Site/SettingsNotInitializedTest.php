<?php


namespace Drupal\Tests\Core\Site;

use Drupal\Core\Site\Settings\SettingsNotInitializedException;

/**
 * Tests the case where Settings::$instance is not initialized yet.
 *
 * @see \Drupal\Core\Site\Settings::$instance
 *
 * @group Site
 */
class SettingsNotInitializedTest extends \PHPUnit_Framework_TestCase {

  /**
   * Tests the case where Settings::$instance is not initialized.
   *
   * @dataProvider methodsWithExceptionProvider
   *
   * @param string $method
   *   The static method to call on \Drupal::
   * @param array $args
   *   Arguments to pass into \Drupal::$method(..)
   */
  public function testSettingsNotInitializedException($method, $args = array()) {
    try {
      call_user_func_array(['Drupal\Core\Site\Settings', $method], $args);
    }
    catch (SettingsNotInitializedException $e) {
      $this->assertEquals(
        'Settings::$instance is not initialized yet.',
        $e->getMessage());
      return;
    }
    $this->fail("Settings::$method() should trigger an exception, if Settings::\$instance is not initialized.");
  }

  /**
   * Data provider for one method, see "@see" below.
   *
   * @see testSettingsNotInitializedException()
   */
  public function methodsWithExceptionProvider() {
    return array(
      ['get', ['example_key']],
      ['getAll'],
      ['getHashSalt'],
    );
  }

}
