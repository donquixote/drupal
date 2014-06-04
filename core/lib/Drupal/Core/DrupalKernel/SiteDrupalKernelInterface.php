<?php


namespace Drupal\Core\DrupalKernel;


/**
 * Represents the part of the DrupalKernel that is ready before bootstrap.
 */
interface SiteDrupalKernelInterface extends RawDrupalKernelInterface {

  /**
   * Gets the site path.
   *
   * @return string
   *   The current site path.
   */
  public function getSitePath();

  /**
   * Boots the current kernel.
   *
   * @return \Drupal\Core\DrupalKernelInterface
   */
  public function boot();

} 
