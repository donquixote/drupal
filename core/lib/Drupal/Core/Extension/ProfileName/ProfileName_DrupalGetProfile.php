<?php

namespace Drupal\Core\Extension\ProfileName;

class ProfileName_DrupalGetProfile implements ProfileNameInterface {

  /**
   * @return string|null
   */
  public function getProfileName() {
    if (!function_exists('drupal_get_profile')) {
      throw new \RuntimeException('Function drupal_get_profile() is not defined.');
    }
    return drupal_get_profile() ?: NULL;
  }
}
