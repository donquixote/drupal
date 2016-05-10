<?php

namespace Drupal\Core\Extension\ProfileName;

class ProfileName_DrupalGetProfile implements ProfileNameInterface {

  /**
   * @return string|null
   */
  public function getProfileName() {
    return drupal_get_profile() ?: NULL;
  }
}
