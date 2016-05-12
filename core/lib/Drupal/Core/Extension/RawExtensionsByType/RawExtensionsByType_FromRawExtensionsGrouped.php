<?php

namespace Drupal\Core\Extension\RawExtensionsByType;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ProfileName\ProfileNameInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesUtil;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface;

class RawExtensionsByType_FromRawExtensionsGrouped implements RawExtensionsByTypeInterface {

  /**
   * @var \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface
   */
  private $searchdirPrefixes;

  /**
   * @var \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface
   */
  private $searchdirToRawExtensionsGrouped;

  /**
   * @var \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  private $profileNameProvider;

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $profileNameProvider
   * @param bool $buffered
   *
   * @return \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  public static function create(
    SearchdirPrefixesInterface $searchdirPrefixes,
    SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped,
    ProfileNameInterface $profileNameProvider,
    $buffered = TRUE
  ) {
    $instance = new RawExtensionsByType_FromRawExtensionsGrouped($searchdirPrefixes, $searchdirToRawExtensionsGrouped, $profileNameProvider);
    if ($buffered) {
      $instance = new RawExtensionsByType_Buffer($instance);
    }

    return $instance;
  }

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $profileNameProvider
   */
  public function __construct(SearchdirPrefixesInterface $searchdirPrefixes, SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped, ProfileNameInterface $profileNameProvider) {
    $this->searchdirPrefixes = $searchdirPrefixes;
    $this->searchdirToRawExtensionsGrouped = $searchdirToRawExtensionsGrouped;
    $this->profileNameProvider = $profileNameProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function getRawExtensionsByType() {

    $active_profile_names = $this->getActiveProfileNames();

    $extensions_by_type_and_weight = [];
    foreach ($this->searchdirPrefixes->getSearchdirPrefixWeights() as $searchdir_prefix => $searchdir_weight) {

      /**
       * @var \Drupal\Core\Extension\Extension[][][] $searchdir_extensions_by_type
       *   Format: $[$extension_type][$subdir_name][$name] = $extension
       */
      $searchdir_extensions_by_type = $this->searchdirToRawExtensionsGrouped->getRawExtensionsGrouped($searchdir_prefix);

      $searchdir_active_profile_dirs = [];
      foreach ($active_profile_names as $active_profile_name) {
        if (isset($searchdir_extensions_by_type['profile']['profiles'][$active_profile_name])) {
          $profile = $searchdir_extensions_by_type['profile']['profiles'][$active_profile_name];
          $searchdir_active_profile_dirs[$active_profile_name] = $profile->getPath() . '/';
        }
      }

      foreach ($searchdir_extensions_by_type as $type => $extensions_by_subdir_name) {
        foreach ($extensions_by_subdir_name as $subdir_name => $extensions_by_name) {
          if ($subdir_name === 'profiles' && $type !== 'profile') {
            // Only accept those extensions that live in an active profile's directory.
            if ($searchdir_active_profile_dirs === []) {
              // No active profiles in the current search directory.
              continue;
            }
            $extensions_by_name = array_filter(
              $extensions_by_name,
              function (Extension $extension) use ($searchdir_active_profile_dirs) {
                foreach ($searchdir_active_profile_dirs as $profile_dir) {
                  if (0 === strpos($extension->getPathname(), $profile_dir)) {
                    return TRUE;
                  }
                }
                return FALSE;
              });
            $searchdir_weight = SearchdirPrefixesUtil::ORIGIN_PROFILE;
          }

          foreach ($extensions_by_name as $name => $extension) {
            $extensions_by_type_and_weight[$type][$searchdir_weight][$name] = $extension;
          }
        }
      }
    }

    $extensions_by_type = [];
    foreach ($extensions_by_type_and_weight as $type => $type_extensions_by_weight) {
      krsort($type_extensions_by_weight);
      $type_extensions = [];
      foreach ($type_extensions_by_weight as $weight_extensions_by_name) {
        $type_extensions += $weight_extensions_by_name;
      }
      $extensions_by_type[$type] = $type_extensions;
    }

    return $extensions_by_type;
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
