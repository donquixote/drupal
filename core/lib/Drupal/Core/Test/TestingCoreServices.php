<?php


namespace Drupal\Core\Test;

use Drupal\Core\CoreContainer\CoreServices;

/**
 * Dedicated core service container for simpletest requests.
 */
class TestingCoreServices extends CoreServices {

  /**
   * @return self
   */
  public static function create() {
    return (new self)
      ->setEnvironment('testing');
  }

  /**
   * Exits with "403 Forbidden", if not in an internal simpletest request.
   *
   * @todo Find a better place for this.
   */
  public function exitIfNoTest() {
    // Include bootstrap.inc, where drupal_valid_test_ua() is defined.
    $this->StaticContext->BootstrapIncIncluded;
    if (!\drupal_valid_test_ua()) {
      $request = $this->Request;
      header($request->server->get('SERVER_PROTOCOL') . ' 403 Forbidden');
      exit;
    }
  }

} 
