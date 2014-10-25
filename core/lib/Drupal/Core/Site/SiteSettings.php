<?php


namespace Drupal\Core\Site;

/**
 * @property string|null baseUrl
 * @property string|null cookieDomain
 * @property string[]|null configDirectories
 * @property array settings
 * @property array config
 * @property array[][] databases
 */
class SiteSettings extends MiniContainer {

  /**
   * Array with the settings.
   *
   * @var mixed[]
   */
  private $storage = array();

  /**
   * Constructor.
   *
   * @param array $settings
   *   Array with the settings.
   */
  public function __construct(array $settings) {
    $this->storage = $settings;
  }

  /**
   * @param string $name
   * @param mixed $default
   *
   * @return mixed
   */
  public function get($name, $default = NULL) {
    return isset($this->storage[$name])
      ? $this->storage[$name]
      : $default;
  }

  /**
   * @return mixed[]
   */
  public function getAll() {
    return $this->storage;
  }

  /**
   * Gets a salt useful for hardening against SQL injection.
   *
   * @return string
   *   A salt based on information in settings.php, not in the database.
   *
   * @throws \RuntimeException
   */
  public function getHashSalt() {
    $hash_salt = $this->get('hash_salt');
    // This should never happen, as it breaks user logins and many other
    // services. Therefore, explicitly notify the user (developer) by throwing
    // an exception.
    if (empty($hash_salt)) {
      throw new \RuntimeException('Missing $settings[\'hash_salt\'] in settings.php.');
    }

    return $hash_salt;
  }

}
