<?php

namespace Drupal\Core\Extension\ProfileDirs;

class ProfileDirs_Static implements ProfileDirsInterface {

  /**
   * @var string[]
   */
  private $profileDirs;

  /**
   * @param string[] $profileDirs
   *   Format: $['standard'] = 'core/profiles/standard'
   */
  function __construct(array $profileDirs) {
    $this->profileDirs = $profileDirs;
  }

  /**
   * Gets an array of profile directories, keyed by profile name.
   *
   * @return string[]
   *   Format: $['standard'] = 'core/profiles/standard'
   */
  function getProfileDirs() {
    return $this->profileDirs;
  }
}
