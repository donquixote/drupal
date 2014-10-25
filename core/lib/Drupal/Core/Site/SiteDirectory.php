<?php


namespace Drupal\Core\Site;

/**
 * Wraps the site path.
 */
class SiteDirectory {

  /**
   * @var string
   */
  private $sitePath;

  /**
   * @param string $site_path
   *
   * @throws \Exception
   */
  function __construct($site_path) {
    if (!is_string($site_path)) {
      throw new \Exception("Invalid site path.");
    }
    $this->sitePath = $site_path;
  }

  /**
   * @return string
   */
  function getSitePath() {
    return $this->sitePath;
  }

}
