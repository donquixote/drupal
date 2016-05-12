<?php

namespace Drupal\Core\Extension\RawExtensionsByType;

use Drupal\Core\Extension\ProfileName\ProfileNameInterface;

class RawExtensionsByType_ActiveProfileAsModule implements RawExtensionsByTypeInterface {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  private $profileNameProvider;

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $decorated
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $profileNameProvider
   */
  public function __construct(RawExtensionsByTypeInterface $decorated, ProfileNameInterface $profileNameProvider) {
    $this->decorated = $decorated;
    $this->profileNameProvider = $profileNameProvider;
  }

  /**
   * @return \Drupal\Core\Extension\Extension[][]
   *   Format: $[$extension_type][$extension_name] = $extension
   */
  public function getRawExtensionsByType() {
    $extensions_by_type_and_name = $this->decorated->getRawExtensionsByType();
    if (NULL !== $active_profile_name = $this->profileNameProvider->getProfileName()) {
      if (isset($extensions_by_type_and_name['profile'][$active_profile_name])) {
        $extensions_by_type_and_name['module'][$active_profile_name] = $extensions_by_type_and_name['profile'][$active_profile_name];
      }
    }
    return $extensions_by_type_and_name;
  }
}
