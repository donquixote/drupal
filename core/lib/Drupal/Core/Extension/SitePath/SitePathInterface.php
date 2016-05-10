<?php

namespace Drupal\Core\Extension\SitePath;

/**
 * Data provider for the site path, e.g. 'sites/default'.
 */
interface SitePathInterface {

  /**
   * @return string|null
   */
  public function getSitePath();

}
