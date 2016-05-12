<?php

namespace Drupal\Core\Extension\FilesByType;

use Drupal\Core\Extension\ProfileName\ProfileNameInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesUtil;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface;

class FilesByType_FromFilesGrouped implements FilesByTypeInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface
   */
  private $searchdirPrefixes;

  /**
   * @var \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  private $searchdirToFilesGrouped;

  /**
   * @var \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  private $profileNameProvider;

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $searchdirToFilesGrouped
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $profileNameProvider
   */
  public function __construct(SearchdirPrefixesInterface $searchdirPrefixes, SearchdirToFilesGroupedInterface $searchdirToFilesGrouped, ProfileNameInterface $profileNameProvider) {
    $this->searchdirPrefixes = $searchdirPrefixes;
    $this->searchdirToFilesGrouped = $searchdirToFilesGrouped;
    $this->profileNameProvider = $profileNameProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilesByType() {

    $active_profile_names = $this->getActiveProfileNames();

    $files_by_type_and_weight = [];
    foreach ($this->searchdirPrefixes->getSearchdirPrefixWeights() as $searchdir_prefix => $searchdir_weight) {

      /**
       * @var string[][][]
       *   Format: $[$extension_type][$subdir_name][$name] = $file
       */
      $searchdir_files_by_type = $this->searchdirToFilesGrouped->getFilesGrouped($searchdir_prefix);

      $regex_snippets = [];
      foreach ($active_profile_names as $active_profile_name) {
        if (isset($searchdir_files_by_type['profile']['profiles'][$active_profile_name])) {
          $profile_info_file = $searchdir_files_by_type['profile']['profiles'][$active_profile_name];
          $regex_snippets[$active_profile_name] = preg_quote(dirname($profile_info_file), '@');
        }
      }

      $regex = NULL;
      if ($regex_snippets !== []) {
        $regex = '@^(' . implode('|', $regex_snippets) . ')/@';
      }

      foreach ($searchdir_files_by_type as $type => $files_by_subdir_name) {
        foreach ($files_by_subdir_name as $subdir_name => $files_by_name) {
          if ($subdir_name === 'profiles' && $type !== 'profile') {
            // Only accept those extension files that live in an active profile's directory.
            if ($regex === NULL) {
              // No active profiles in the current search directory.
              continue;
            }
            $files_by_name = preg_grep($regex, $files_by_name);
            $searchdir_weight = SearchdirPrefixesUtil::ORIGIN_PROFILE;
          }

          foreach ($files_by_name as $name => $file) {
            $files_by_type_and_weight[$type][$searchdir_weight][$name] = $file;
          }
        }
      }
    }

    $files_by_type_and_name = [];
    foreach ($files_by_type_and_weight as $type => $type_files_by_weight) {
      krsort($type_files_by_weight);
      $type_files_by_name = [];
      foreach ($type_files_by_weight as $weight_files_by_name) {
        $type_files_by_name += $weight_files_by_name;
      }
      $files_by_type_and_name[$type] = $type_files_by_name;
    }

    return $files_by_type_and_name;
  }

  /**
   * @return string[]
   */
  private function getActiveProfileNames() {
    $profile_names = [];
    if (NULL !== $profile_name = $this->profileNameProvider->getProfileName()) {
      $profile_names[] = $profile_name;
    }
    return $profile_names;
  }
}
