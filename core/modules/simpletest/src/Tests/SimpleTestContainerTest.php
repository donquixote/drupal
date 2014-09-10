<?php

/**
 * @file
 * Definition of \Drupal\simpletest\Tests\SimpleTestTest.
 */

namespace Drupal\simpletest\Tests;

use Drupal\Core\Database\Driver\pgsql\Select;
use Drupal\simpletest\WebTestBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests the Simpletest UI test runner and internal browser.
 */
class SimpleTestContainerTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  # public static $modules = array('simpletest', 'test_page_test');

  public static function getInfo() {
    return array(
      'name' => 'SimpleTest container management',
      'description' => "Check that tests run with a separate container.",
      'group' => 'SimpleTest'
    );
  }

  function setUp() {
    parent::setUp();
  }

  /**
   * Tests that the test container is not identical with the original container.
   */
  function testContainerReplaced() {
    $this->assertTrue(
      $this->originalContainer !== $this->container,
      "The test container is different from the original container.");
    $this->assertTrue(
      $this->originalContainer instanceof ContainerInterface,
      "The original container is a ContainerInterface instance.");
    $this->assertTrue(
      $this->container instanceof ContainerInterface,
      "The test container is a ContainerInterface instance.");
  }

}
