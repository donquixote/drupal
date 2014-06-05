<?php


namespace Drupal\Core\Test;

use Drupal\Core\CoreContainer\CoreServices;

/**
 * Dedicated core service container for test-runner.sh.
 */
class TestRunnerCoreServices extends CoreServices {

  /**
   * @return self
   */
  public static function create() {
    return (new self)
      ->setEnvironment('test_runner');
  }

  /**
   * {@inheritdoc}
   */
  protected function getRawDrupalKernel() {

    // Include our bootstrap file.
    $this->StaticContext->BootstrapIncIncluded;

    // Use TestRunnerKernel instead of DrupalKernel.
    return new TestRunnerKernel(
      $this->parameters->Environment,
      $this->ClassLoader);
  }

} 
