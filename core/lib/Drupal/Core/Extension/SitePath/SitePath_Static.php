<?php

namespace Drupal\Core\Extension\SitePath;

class SitePath_Static implements SitePathInterface {

  /**
   * @var string
   */
  private $sitePath;

  /**
   * @param string $sitePath
   */
  public function __construct($sitePath) {
    $this->sitePath = $sitePath;
  }

  /**
   * @return string|null
   */
  public function getSitePath() {
    return $this->sitePath;
  }
}
