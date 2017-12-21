<?php
/**
 * @file
 * FinderPlugin for xautoload to handle the non-PSR-0 compliant Color class.
 */

namespace Drupal\phpcolors\xautoload;

/**
 * Implements \xautoload\FinderPlugin\Interface
 */
class phpColorsLibraryFinderPlugin implements \xautoload_FinderPlugin_Interface {

  protected $libDir;

  /**
   * Plugin constructor.
   *
   * @param string $lib_dir
   *   Path to the library folder to search within.
   */
  public function __construct($lib_dir) {

    $this->libDir = $lib_dir;
  }


  /**
   * Provides a path to the phpColors library.
   *
   * @param xautoload_InjectedAPI_findFile $api
   *   An object with a suggestFile() method.
   *   We are supposed to suggest files until suggestFile() returns TRUE, or we
   *   have no more suggestions.
   *
   * @param string $logical_base_path
   *   The key that this plugin was registered with.
   *   With trailing DIRECTORY_SEPARATOR.
   *
   * @param string $relative_path
   *   Second part of the canonical path, ending with '.php'.
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function findFile($api, $logical_base_path, $relative_path) {

    if ('.' === dirname($relative_path)) {

      $basename = strtolower(basename($relative_path));
      $path = $this->libDir . '/src/' . $basename;

      if ($api->suggestFile($path)) {

        return TRUE;
      }
    }

    return FALSE;
  }
}
