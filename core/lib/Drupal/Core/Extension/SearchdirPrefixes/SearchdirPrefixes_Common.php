<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

class SearchdirPrefixes_Common implements SearchdirPrefixesInterface {

  /**
   * The site path, or NULL.
   *
   * @var string|null
   *   E.g. 'sites/default'.
   */
  private $sitePath;

  /**
   * @param string $sitePath
   *   The site path, e.g. 'sites/default'.
   *   If the site path is NULL, one will be automatically determined from
   *   global state.
   */
  public function __construct($sitePath = NULL) {
    $this->sitePath = $sitePath;
  }

  /**
   * @return int[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   */
  public function getSearchdirPrefixWeights() {

    $prefix_weights = SearchdirPrefixesUtil::getBasicSearchdirPrefixWeights();

    if (NULL !== $site_path = $this->getSitePath()) {
      $prefix_weights[$site_path . '/'] = SearchdirPrefixesUtil::ORIGIN_SITE;
    }

    return $prefix_weights;
  }

  /**
   * Returns the site path.
   *
   * If $this->sitePath is not set, one will be automatically determined.
   *
   * @return null|string
   *   E.g. 'sites/default'.
   */
  private function getSitePath() {
    if ($this->sitePath !== NULL) {
      // The injected site path is preferred.
      return $this->sitePath;
    }
    elseif (\Drupal::hasService('kernel')) {
      // A kernel may not be available yet during install.
      return (string)\Drupal::service('site.path');
    }
    else {
      // If no kernel is available, e.g. when looking for profiles during
      // install, determine one automatically.
      return DrupalKernel::findSitePath(Request::createFromGlobals());
    }
  }
}
