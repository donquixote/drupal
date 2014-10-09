<?php


namespace Drupal\Core\DrupalKernel;

use Symfony\Component\HttpFoundation\Request;

/**
 * Represents the part of the DrupalKernel that is ready before the site
 * directory and site settings.
 */
interface RawDrupalKernelInterface {

  /**
   * Set the current site path.
   *
   * @param $path
   *   The current site path.
   *
   * @return SiteDrupalKernelInterface
   */
  public function setSitePath($path);

} 
