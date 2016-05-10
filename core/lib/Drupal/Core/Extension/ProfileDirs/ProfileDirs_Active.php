<?php

namespace Drupal\Core\Extension\ProfileDirs;

use Drupal\Core\Extension\FilesByName\FilesByNameInterface;
use Drupal\Core\Extension\ProfileName\ProfileNameInterface;

/**
 * Implementation that only returns the active profile.
 */
class ProfileDirs_Active implements ProfileDirsInterface {

  /**
   * @var \Drupal\Core\Extension\FilesByName\FilesByNameInterface
   */
  private $profileFilesByName;

  /**
   * @var \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  private $profileNameProvider;

  /**
   * @param \Drupal\Core\Extension\FilesByName\FilesByNameInterface $profileFilesByName
   *   Provider for the filenames of all profiles.
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $profileNameProvider
   */
  function __construct(FilesByNameInterface $profileFilesByName, ProfileNameInterface $profileNameProvider) {
    $this->profileFilesByName = $profileFilesByName;
    $this->profileNameProvider = $profileNameProvider;
  }

  /**
   * Gets an array of profile directories, keyed by profile name.
   *
   * @return string[]
   *   Format: $['standard'] = 'core/profiles/standard'
   */
  function getProfileDirs() {

    $active_profile_name = $this->profileNameProvider->getProfileName();
    if ($active_profile_name === NULL) {
      return [];
    }

    $all_profile_files = $this->profileFilesByName->getFilesByName();

    $return = [];
    if (array_key_exists($active_profile_name, $all_profile_files)) {
      $return[$active_profile_name] = dirname($all_profile_files[$active_profile_name]);
    }

    return $return;
  }
}
