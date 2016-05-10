<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

/**
 * Provides prefixes for directories that will be searched for extensions.
 */
interface SearchdirPrefixesInterface {

  /**
   * Gets an array of directory weights.
   *
   * The keys are the directories, and the values are weights, representing the
   * precedence of search directories.
   *
   * Extensions in a directory with a higher weight override extensions in
   * lower-weight directories with the same name.
   *
   * @return int[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   *
   * @see \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesUtil
   */
  public function getSearchdirPrefixWeights();

}
