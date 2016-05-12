<?php

namespace Drupal\Core\Extension\RawExtensionsByName;

use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface;
use Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesUtil;

class RawExtensionsByName_NonProfile extends RawExtensionsByNameBase {

  /**
   * @var \Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface
   */
  private $activeProfileDirs;

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped
   * @param string $type
   * @param \Drupal\Core\Extension\ProfileDirs\ProfileDirsInterface $activeProfileDirs
   */
  public function __construct(
    SearchdirPrefixesInterface $searchdirPrefixes,
    SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped,
    $type,
    ProfileDirsInterface $activeProfileDirs
  ) {
    parent::__construct($searchdirPrefixes, $searchdirToRawExtensionsGrouped, $type);
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
   * @return \Drupal\Core\Extension\Extension[][][]
   *   Format: $[$searchdir_prefix][$subdir_name][$extension_name] = $file
   *   E.g. $['core/']['modules']['system'] = 'core/modules/system/system.info.yml'
   */
  protected function getExtensionsGroupedBySearchdirPrefix() {

    $extensions_grouped_by_searchdir_prefix = parent::getExtensionsGroupedBySearchdirPrefix();
    $active_profile_dirs_by_searchdir = $this->getActiveProfileDirsBySearchdir();

    // Deal with non-profile extensions in profile directories.
    foreach ($extensions_grouped_by_searchdir_prefix as $searchdir_prefix => $extensions_by_subdir_name) {
      if (!array_key_exists('profiles', $extensions_by_subdir_name)) {
        // No extensions of this type were found in $searchdir_prefix . 'profiles'.
        continue;
      }
      // Remove all extensions of this type found in $searchdir_prefix . 'profiles'.
      unset($extensions_grouped_by_searchdir_prefix[$searchdir_prefix]['profiles']);
      // Check if the $searchdir_prefix . 'profiles' directory
      if (!array_key_exists($searchdir_prefix . 'profiles', $active_profile_dirs_by_searchdir)) {

        continue;
      }
      foreach ($active_profile_dirs_by_searchdir[$searchdir_prefix . 'profiles'] as $profile_name => $profile_dir) {
        $extensions_grouped_by_searchdir_prefix['/@_active_profile_extensions_@/'][] = preg_grep(
          '@^' . preg_quote($profile_dir, '@') . '/@',
          $extensions_by_subdir_name['profiles']);
      }
    }

    return $extensions_grouped_by_searchdir_prefix;
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
