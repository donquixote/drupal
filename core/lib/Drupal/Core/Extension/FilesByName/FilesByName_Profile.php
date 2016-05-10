<?php

namespace Drupal\Core\Extension\FilesByName;

use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface;

class FilesByName_Profile extends FilesByNameBase {

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $searchdirToFilesGrouped
   *
   * @return \Drupal\Core\Extension\FilesByName\FilesByName_Profile
   */
  public function __construct(SearchdirPrefixesInterface $searchdirPrefixes, SearchdirToFilesGroupedInterface $searchdirToFilesGrouped) {
    parent::__construct($searchdirPrefixes, $searchdirToFilesGrouped, 'profile');
  }
}
