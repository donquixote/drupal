<?php


namespace Drupal\Core\Site;


use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Service to find a site path based on the request.
 */
class SitePathFinder {

  /**
   * @param Request $request
   * @param bool $require_settings
   *
   * @return string
   *
   * @see DrupalKernelInterface::findSitePath()
   */
  public function findSitePath(Request $request, $require_settings = TRUE) {
    // @todo Move method body here, and eliminate the static method on DrupalKernel.
    return DrupalKernel::findSitePath($request, $require_settings);
  }

} 
