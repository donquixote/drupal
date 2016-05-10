<?php

namespace Drupal\Core\Extension\SitePath;

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

class SitePath_Common implements SitePathInterface {

  /**
   * @var string|null
   */
  private $sitePath;

  /**
   * @param string|null $sitePath
   */
  public function __construct($sitePath = NULL) {
    $this->sitePath = $sitePath;
  }

  /**
   * Finds the site-specific directory to search.
   *
   * Since we are using this method to discover extensions including profiles,
   * we might be doing this at install time. Therefore Kernel service is not
   * always available, but is preferred.
   *
   * @return string|null
   */
  public function getSitePath() {
    if (\Drupal::hasService('kernel')) {
      return \Drupal::service('site.path');
    }
    elseif ($this->sitePath) {
      return $this->sitePath;
    }
    else {
      return DrupalKernel::findSitePath(Request::createFromGlobals());
    }
  }
}
