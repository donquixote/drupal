<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

final class SearchdirPrefixesUtil {

  /**
   * Origin directory weight: Core.
   */
  const ORIGIN_CORE = 0;

  /**
   * Origin directory weight: Installation profile.
   */
  const ORIGIN_PROFILE = 1;

  /**
   * Origin directory weight: sites/all.
   */
  const ORIGIN_SITES_ALL = 2;

  /**
   * Origin directory weight: Site-wide directory.
   */
  const ORIGIN_ROOT = 3;

  /**
   * Origin directory weight: Parent site directory of a test site environment.
   */
  const ORIGIN_PARENT_SITE = 4;

  /**
   * Origin directory weight: Site-specific directory.
   */
  const ORIGIN_SITE = 5;

  /**
   * @param string|null $site_path
   * @param string|null $simpletest_parent_site
   *
   * @return int[]
   */
  public static function getSearchdirPrefixWeights($site_path = NULL, $simpletest_parent_site = NULL) {

    $prefix_weights = [
      // Search the core directory.
      'core/' => static::ORIGIN_CORE,

      // Search the legacy sites/all directory.
      'sites/all/' => static::ORIGIN_SITES_ALL,

      // Search for contributed and custom extensions in top-level directories.
      // The scan uses a whitelist to limit recursion to the expected extension
      // type specific directory names only.
      '' => static::ORIGIN_ROOT,
    ];

    if ($site_path) {
      $prefix_weights[$site_path . '/'] = SearchdirPrefixesUtil::ORIGIN_SITE;
    }

    // Simpletest uses the regular built-in multi-site functionality of Drupal
    // for running web tests. As a consequence, extensions of the parent site
    // located in a different site-specific directory are not discovered in a
    // test site environment, because the site directories are not the same.
    // Therefore, add the site directory of the parent site to the search paths,
    // so that contained extensions are still discovered.
    // @see \Drupal\simpletest\WebTestBase::setUp()
    if ($simpletest_parent_site) {
      $prefix_weights[$simpletest_parent_site . '/'] = SearchdirPrefixesUtil::ORIGIN_PARENT_SITE;
    }

    return $prefix_weights;
  }

}
