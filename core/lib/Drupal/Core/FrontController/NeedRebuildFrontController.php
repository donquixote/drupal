<?php


namespace Drupal\Core\FrontController;

use Drupal\Core\Site\Settings;

/**
 * Front controller that shows a "needs rebuild" message.
 */
class NeedRebuildFrontController implements FrontControllerInterface {

  /**
   * @var \Exception
   */
  private $exception;

  /**
   * @param \Exception $exception
   */
  function __construct(\Exception $exception) {
    $this->exception = $exception;
  }

  /**
   * Executes the front controller operation.
   */
  function sendResponse() {
    $message = 'If you have just changed code (for example deployed a new module or moved an existing one) read <a href="http://drupal.org/documentation/rebuild">http://drupal.org/documentation/rebuild</a>';
    if (Settings::get('rebuild_access', FALSE)) {
      $rebuild_path = $GLOBALS['base_url'] . '/rebuild.php';
      $message .= " or run the <a href=\"$rebuild_path\">rebuild script</a>";
    }

    // Set the response code manually. Otherwise, this response will default to
    // a 200.
    http_response_code(500);
    print $message;
    throw $this->exception;
  }
}
