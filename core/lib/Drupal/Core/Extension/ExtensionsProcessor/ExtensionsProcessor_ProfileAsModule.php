<?php

namespace Drupal\Core\Extension\ExtensionsProcessor;

use Drupal\Core\Extension\ProfileName\ProfileNameInterface;

class ExtensionsProcessor_ProfileAsModule implements ExtensionsProcessorInterface {

  /**
   * @var \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  private $activeProfileNameProvider;

  /**
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $activeProfileNameProvider
   */
  public function __construct(ProfileNameInterface $activeProfileNameProvider) {
    $this->activeProfileNameProvider = $activeProfileNameProvider;
  }

  /**
   * @param \Drupal\Core\Extension\Extension[] $extensions
   */
  public function processExtensions(array $extensions) {

    if (NULL === $active_profile_name = $this->activeProfileNameProvider->getProfileName()) {
      return;
    }

    if (!array_key_exists($active_profile_name, $extensions)) {
      return;
    }

    $active_profile = $extensions[$active_profile_name];

    if (!isset($active_profile->info['hidden'])) {
      $active_profile->info['hidden']  = TRUE;
    }

    // The installation profile is required, if it's a valid module.
    $active_profile->info['required'] = TRUE;

    // Add a default distribution name if the profile did not provide one.
    // @see install_profile_info()
    // @see drupal_install_profile_distribution_name()
    if (!isset($active_profile->info['distribution']['name'])) {
      $active_profile->info['distribution']['name'] = 'Drupal';
    }

    // Installation profile hooks are always executed last.
    $active_profile->weight = 1000;
  }

}
