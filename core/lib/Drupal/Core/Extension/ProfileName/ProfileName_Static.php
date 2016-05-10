<?php

namespace Drupal\Core\Extension\ProfileName;

class ProfileName_Static implements ProfileNameInterface {

  /**
   * @var string
   */
  private $profileName;

  /**
   * @param string $profileName
   */
  public function __construct($profileName) {
    $this->profileName = $profileName;
  }

  /**
   * @return string|null
   */
  public function getProfileName() {
    return $this->profileName;
  }
}
