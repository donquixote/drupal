<?php

namespace Drupal\Core\Extension\ProfileDirs;

/**
 * Data provider to deliver an array of profile directories.
 */
interface ProfileDirsInterface {

  /**
   * Gets an array of profile directories, keyed by profile name.
   *
   * @return string[]
   *   Format: $['standard'] = 'core/profiles/standard'
   */
  function getProfileDirs();

}
