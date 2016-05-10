<?php

namespace Drupal\Core\Extension\FilesByName;

use Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesUtil;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface;

class FilesByName_NonProfile extends FilesByNameBase {

  /**
   * @var \Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface
   */
  private $activeProfileDirs;

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $searchdirToFilesGrouped
   * @param string $extensionType
   *   E.g. 'module' or 'theme'.
   * @param \Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface $activeProfileDirs
   */
  function __construct(
    SearchdirPrefixesInterface $searchdirPrefixes,
    SearchdirToFilesGroupedInterface $searchdirToFilesGrouped,
    $extensionType,
    ProfileDirsInterface $activeProfileDirs
  ) {
    parent::__construct($searchdirPrefixes, $searchdirToFilesGrouped, $extensionType);
    $this->activeProfileDirs = $activeProfileDirs;
  }

  /**
   * @return int[]
   *   Format: $['core/'] = 0
   */
  protected function getModifiedSearchdirPrefixWeights() {
    $searchdir_prefix_weights = parent::getModifiedSearchdirPrefixWeights();
    $searchdir_prefix_weights['/@_active_profile_extensions_@/'] = SearchdirPrefixesUtil::ORIGIN_PROFILE;
    return $searchdir_prefix_weights;
  }

  /**
   * @return string[][][]
   *   Format: $[$searchdir_prefix][$subdir_name][$extension_name] = $file
   *   E.g. $['core/']['modules']['system'] = 'core/modules/system/system.info.yml'
   */
  protected function getFilesGroupedBySearchdirPrefix() {
    $files_grouped_by_searchdir_prefix = parent::getFilesGroupedBySearchdirPrefix();

    $active_profile_dirs_by_searchdir = $this->getActiveProfileDirsBySearchdir();

    // Deal with non-profile extensions in profile directories.
    foreach ($files_grouped_by_searchdir_prefix as $searchdir_prefix => $files_by_subdir_name) {
      if (array_key_exists('profiles', $files_by_subdir_name)) {
        unset($files_grouped_by_searchdir_prefix[$searchdir_prefix]['profiles']);
        if (array_key_exists($searchdir_prefix . 'profiles', $active_profile_dirs_by_searchdir)) {
          foreach ($active_profile_dirs_by_searchdir[$searchdir_prefix . 'profiles'] as $profile_name => $profile_dir) {
            $files_grouped_by_searchdir_prefix['/@_active_profile_extensions_@/'][] = preg_grep(
              '@^' . preg_quote($profile_dir, '@') . '/@',
              $files_by_subdir_name['profiles']);
          }
        }
      }
    }

    return $files_grouped_by_searchdir_prefix;
  }

  /**
   * @return string[][]
   *   Format: $['core/profiles']['standard'] = 'core/profiles/standard'
   */
  private function getActiveProfileDirsBySearchdir() {
    $active_profile_dirs_by_searchdir = [];
    foreach ($this->activeProfileDirs->getProfileDirs() as $profile_name => $profile_dir) {
      if (FALSE !== $pos = strpos($profile_dir, 'profiles/')) {
        $profile_searchdir = substr($profile_dir, 0, $pos + 8);
        $active_profile_dirs_by_searchdir[$profile_searchdir][$profile_name] = $profile_dir;
      }
    }
    return $active_profile_dirs_by_searchdir;
  }
}
