<?php


namespace Drupal\Core\Site\Settings;

use Drupal\Core\Site\Settings\SettingsInterface;
use Symfony\Component\Debug\Exception\ContextErrorException;

/**
 * Placeholder settings object.
 *
 * Throws exceptions instead of doing anything.
 *
 * @see \Drupal\Core\Site\Settings::$instance
 */
class PlaceholderSettings implements SettingsInterface {

  /**
   * @var string
   */
  private $message;

  /**
   * Constructor
   *
   * @param string $message
   *   Message for exceptions.
   */
  function __construct($message = NULL) {
    $this->message = isset($message)
      ? $message
      : 'Settings not initialized.';
  }

  /**
   * {@inheritdoc}
   */
  public function get($name, $default = NULL) {
    throw new SettingsNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function getAll() {
    throw new SettingsNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function getHashSalt() {
    throw new SettingsNotInitializedException($this->message);
  }
}
