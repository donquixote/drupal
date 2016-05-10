<?php

namespace Drupal\Core\Extension\List_\Raw;

use Drupal\Core\Extension\ProfileName\ProfileNameInterface;

/**
 * Decorator that adds the active profile to the list.
 */
class RawExtensionList_ProfileAsModule implements RawExtensionListInterface {

  /**
   * @var \Drupal\Core\Extension\List_\Raw\RawExtensionListInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\List_\Raw\RawExtensionListInterface
   */
  private $profileList;

  /**
   * @var \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  private $profileNameProvider;

  /**
   * @param \Drupal\Core\Extension\List_\Raw\RawExtensionListInterface $decorated
   * @param \Drupal\Core\Extension\List_\Raw\RawExtensionListInterface $profileList
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $profileNameProvider
   */
  public function __construct(RawExtensionListInterface $decorated, RawExtensionListInterface $profileList, ProfileNameInterface $profileNameProvider) {
    $this->decorated = $decorated;
    $this->profileList = $profileList;
    $this->profileNameProvider = $profileNameProvider;
  }

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset() {
    $this->decorated->reset();
    $this->profileList->reset();
    return $this;
  }

  /**
   * Returns all available extensions, with $extension->info possibly NOT yet
   * filled in.
   *
   * It can happen that other components further modify these objects, and add
   * the ->info array and more.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getRawExtensions() {
    $extensions = $this->decorated->getRawExtensions();
    $active_profile_name = $this->profileNameProvider->getProfileName();
    if ($active_profile_name !== NULL) {
      $profiles = $this->profileList->getRawExtensions();
      if (array_key_exists($active_profile_name, $profiles)) {
        $extensions[$active_profile_name] = $profiles[$active_profile_name];
      }
    }
    return $extensions;
  }
}
