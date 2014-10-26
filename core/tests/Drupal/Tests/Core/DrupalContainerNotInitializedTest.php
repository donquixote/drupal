<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\DrupalTest.
 */

namespace Drupal\Tests\Core;

use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use Drupal\Tests\UnitTestCase;
use Drupal\Core\Url;

/**
 * Tests the case where Drupal::$container is not initialized.
 *
 * @group DrupalTest
 */
class DrupalContainerNotInitializedTest extends UnitTestCase {

  /**
   * Overrides the parent setUp() method, to make sure that \Drupal::$container
   * is not initialized.
   */
  protected function setUp() {
    // Do nothing here, and do not call the parent method.
  }

  /**
   * Tests the case where \Drupal::$container is not initialized.
   *
   * @dataProvider methodsWithReturnProvider
   *
   * @param string $method
   *   The static method to call on \Drupal::
   * @param mixed $expected
   *   Expected return value.
   * @param array $args
   *   Arguments to pass into \Drupal::$method(..)
   */
  public function testContainerNotInitializedReturn($method, $expected, $args = array()) {
    $result = call_user_func_array(['Drupal', $method], $args);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the case where \Drupal::$container is not initialized.
   *
   * @dataProvider methodsWithExceptionProvider
   *
   * @param string $method
   *   The static method to call on \Drupal::
   * @param array $args
   *   Arguments to pass into \Drupal::$method(..)
   */
  public function testContainerNotInitializedException($method, $args = array()) {
    try {
      call_user_func_array(['Drupal', $method], $args);
    }
    catch (ContainerNotInitializedException $e) {
      $this->assertEquals(
        '\Drupal::$container is not initialized yet. \Drupal::setContainer() must be called with a real container.',
        $e->getMessage());
      return;
    }
    $this->fail('\Drupal::service() should trigger an exception, if no container is available.');
  }

  /**
   * Tests the case where \Drupal::$container was unset.
   *
   * @dataProvider methodsWithReturnProvider
   *
   * @param string $method
   *   The static method to call on \Drupal::
   * @param mixed $expected
   *   Expected return value.
   * @param array $args
   *   Arguments to pass into \Drupal::$method(..)
   */
  public function testUnsetContainerReturn($method, $expected, $args = array()) {
    \Drupal::unsetContainer(__METHOD__);
    $result = call_user_func_array(['Drupal', $method], $args);
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the case where \Drupal::$container was unset.
   *
   * @dataProvider methodsWithExceptionProvider
   *
   * @param string $method
   *   The static method to call on \Drupal::
   * @param array $args
   *   Arguments to pass into \Drupal::$method(..)
   */
  public function testUnsetContainerException($method, $args = array()) {
    try {
      \Drupal::unsetContainer(__METHOD__);
      call_user_func_array(['Drupal', $method], $args);
    }
    catch (ContainerNotInitializedException $e) {
      $this->assertEquals(__METHOD__, $e->getMessage());
      return;
    }
    $this->fail('\Drupal::service() should trigger an exception, if no container is available.');
  }

  /**
   * Data provider for two methods, see "@see" below.
   *
   * @return array[]
   *
   * @see testContainerNotInitializedReturn()
   * @see testUnsetContainerReturn()
   */
  public function methodsWithReturnProvider() {
    return array(
      ['getContainer', NULL],
      ['hasService', FALSE, ['test_service']],
      ['hasRequest', FALSE],
    );
  }

  /**
   * Data provider for two methods, see "@see" below.
   *
   * @return array[]
   *
   * @see testContainerNotInitializedException()
   * @see testUnsetContainerException()
   */
  public function methodsWithExceptionProvider() {
    return array(
      ['service', ['test_service']],
      ['request'],
      ['requestStack'],
      ['routeMatch'],
      ['currentUser'],
      ['entityManager'],
      ['database'],
      ['cache', ['test']],
      ['keyValueExpirable', ['test_collection']],
      ['lock'],
      ['config', ['test_config']],
      ['configFactory'],
      ['queue', ['test_queue', TRUE]],
      ['keyValue', ['test_collection']],
      ['state'],
      ['httpClient'],
      ['entityQuery', ['OR']],
      ['entityQueryAggregate', ['test_entity', 'OR']],
      ['flood', ['test_service']],
      ['moduleHandler', ['test_service']],
      ['typedDataManager', ['test_service']],
      ['token'],
      ['urlGenerator'],
      ['url', ['test_route']],
      ['linkGenerator'],
      ['l', ['Test title', new Url('test_route')]],
      ['translation'],
      ['languageManager'],
      ['csrfToken'],
      ['transliteration'],
      ['formBuilder'],
      ['theme'],
      ['isConfigSyncing'],
      ['logger', ['test_channel']],
      ['menuTree'],
      ['pathValidator'],
      ['accessManager'],
    );
  }

}
