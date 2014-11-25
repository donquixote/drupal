<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\DrupalTest.
 */

namespace Drupal\Tests\Core;

use Drupal\Core\DependencyInjection\ContainerNotInitializedException;
use Drupal\Core\Url;

/**
 * Tests the case where Drupal::$container is not initialized.
 *
 * @group DrupalTest
 */
class DrupalContainerNotInitializedTest extends \PHPUnit_Framework_TestCase {

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
   *
   * @expectedException \Drupal\Core\DependencyInjection\ContainerNotInitializedException
   * @expectedExceptionMessage \Drupal::$container is not initialized yet. \Drupal::setContainer() must be called with a real container.
   */
  public function testContainerNotInitializedException($method, $args = array()) {
    call_user_func_array(['Drupal', $method], $args);
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
   *
   * @expectedException \Drupal\Core\DependencyInjection\ContainerNotInitializedException
   * @expectedExceptionMessage Custom exception message.
   */
  public function testUnsetContainerException($method, $args = array()) {
    \Drupal::unsetContainer('Custom exception message.');
    call_user_func_array(['Drupal', $method], $args);
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
