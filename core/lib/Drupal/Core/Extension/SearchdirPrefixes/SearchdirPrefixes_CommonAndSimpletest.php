<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

use Drupal\Core\Site\Settings;

class SearchdirPrefixes_CommonAndSimpletest extends SearchdirPrefixes_Common {

  /**
   * @return string[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   */
  public function getSearchdirPrefixWeights() {

    $prefix_weights = parent::getSearchdirPrefixWeights();

    // Simpletest uses the regular built-in multi-site functionality of Drupal
    // for running web tests. As a consequence, extensions of the parent site
    // located in a different site-specific directory are not discovered in a
    // test site environment, because the site directories are not the same.
    // Therefore, add the site directory of the parent site to the search paths,
    // so that contained extensions are still discovered.
    // @see \Drupal\simpletest\WebTestBase::setUp()
    if ($parent_site = Settings::get('test_parent_site')) {
      $prefix_weights[$parent_site . '/'] = static::ORIGIN_PARENT_SITE;
    }

    return $prefix_weights;
  }
}
