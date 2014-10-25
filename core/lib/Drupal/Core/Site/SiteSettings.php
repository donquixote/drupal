<?php


namespace Drupal\Core\Site;

/**
 * @ingroup utility
 */
class SiteSettings {

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

}
