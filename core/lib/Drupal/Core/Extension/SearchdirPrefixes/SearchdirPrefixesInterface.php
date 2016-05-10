<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

interface SearchdirPrefixesInterface {

  /**
   * @return string[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   */
  public function getSearchdirPrefixWeights();

}
