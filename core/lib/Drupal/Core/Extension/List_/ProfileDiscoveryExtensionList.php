<?php

namespace Drupal\Core\Extension\List_;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class ProfileDiscoveryExtensionList extends DiscoveryExtensionListBase {

  /**
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Extension\ExtensionDiscovery $extension_discovery
   *
   * @return \Drupal\Core\Extension\List_\ExtensionListInterface
   */
  static function create(
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler,
    ExtensionDiscovery $extension_discovery
  ) {
    return new self(
      'profile',
      $info_parser,
      $module_handler,
      $extension_discovery,
      [
        'dependencies' => [],
        'description' => '',
        'package' => 'Other',
        'version' => NULL,
        'php' => DRUPAL_MINIMUM_PHP,
      ]);
  }

}
