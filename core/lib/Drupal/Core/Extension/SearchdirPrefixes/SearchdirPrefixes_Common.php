<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
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
   * @var bool
   */
  private $supportSimpletest;

  /**
   * @param string $sitePath
   *   The site path, e.g. 'sites/default'.
   *   If the site path is NULL, one will be automatically determined from
   *   global state.
   * @param bool $support_simpletest
   */
  public function __construct($sitePath = NULL, $support_simpletest = TRUE) {
    $this->sitePath = $sitePath;
    $this->supportSimpletest = $support_simpletest;
  }

  /**
   * @return int[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   */
  public function getSearchdirPrefixWeights() {

    $site_path = $this->getSitePath();

    $test_parent_site = NULL;
    if ($this->supportSimpletest) {
      $test_parent_site = Settings::get('test_parent_site') ?: NULL;
    }

    return SearchdirPrefixesUtil::getSearchdirPrefixWeights($site_path, $test_parent_site);
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
