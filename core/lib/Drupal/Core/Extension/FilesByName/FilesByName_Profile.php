<?php

namespace Drupal\Core\Extension\FilesByName;

use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface;

class FilesByName_Profile extends FilesByNameBase {

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface $searchdirToFilesGrouped
   *
   * @return \Drupal\Core\Extension\FilesByName\FilesByName_Profile
   */
  public function __construct(SearchdirPrefixesInterface $searchdirPrefixes, SearchdirPrefixToFilesGroupedInterface $searchdirToFilesGrouped) {
    parent::__construct($searchdirPrefixes, $searchdirToFilesGrouped, 'profile');
  }
}
