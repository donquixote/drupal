<?php

namespace Drupal\Core\Extension\ProfileDirs;

class ProfileDirs_ActiveAndOrTesting implements ProfileDirsInterface {

  /**
   * Gets an array of profile directories, keyed by profile name.
   *
   * @return string[]
   *   Format: $['standard'] = 'core/profiles/standard'
   */
  function getProfileDirs() {

    $profile_directories = array();
    
    $active_profile_name = drupal_get_profile();
    // For SimpleTest to be able to test modules packaged together with a
    // distribution we need to include the profile of the parent site (in
    // which test runs are triggered).
    if (drupal_valid_test_ua() && !drupal_installation_attempted()) {
      $testing_profile_name = \Drupal::config('simpletest.settings')->get('parent_profile');
      if ($testing_profile_name && $testing_profile_name !== $active_profile_name) {
        $profile_directories[] = drupal_get_path('profile', $testing_profile_name);
      }
    }
    
    // In case both profile directories contain the same extension, the actual
    // profile always has precedence.
    if ($active_profile_name) {
      $profile_directories[] = drupal_get_path('profile', $active_profile_name);
    }
    
    return $profile_directories;
  }
}
