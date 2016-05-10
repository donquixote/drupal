<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

abstract class SearchdirPrefixesBase implements SearchdirPrefixesInterface {

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
   * @return string[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   */
  public function getSearchdirPrefixWeights() {

    return [
      // Search the core directory.
      'core/' => static::ORIGIN_CORE,

      // Search the legacy sites/all directory.
      'sites/all/' => static::ORIGIN_SITES_ALL,

      // Search for contributed and custom extensions in top-level directories.
      // The scan uses a whitelist to limit recursion to the expected extension
      // type specific directory names only.
      '' => static::ORIGIN_ROOT,
    ];
  }
}
