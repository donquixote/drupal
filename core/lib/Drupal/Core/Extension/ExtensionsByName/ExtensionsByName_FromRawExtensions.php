<?php

namespace Drupal\Core\Extension\ExtensionsByName;

use Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface;
use Drupal\Core\Extension\RawExtensionsByName\RawExtensionsByNameInterface;

class ExtensionsByName_FromRawExtensions implements ExtensionsByNameInterface {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByName\RawExtensionsByNameInterface
   */
  private $rawExtensionList;

  /**
   * @var \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface
   */
  private $filesToInfo;

  /**
   * @var array
   */
  private $defaults;

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByName\RawExtensionsByNameInterface $rawExtensionList
   * @param \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface $filesToInfo
   * @param array $defaults
   *   Defaults to be merged into the info array parsed from the *.info.yml file.
   */
  public function __construct(RawExtensionsByNameInterface $rawExtensionList, FilesToInfoInterface $filesToInfo, array $defaults) {
    $this->rawExtensionList = $rawExtensionList;
    $this->filesToInfo = $filesToInfo;
    $this->defaults = $defaults;
  }

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset() {
    $this->rawExtensionList->reset();
  }

  /**
   * Returns all available extensions, with $extension->info filled in.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getExtensions() {

    $extensions = $this->rawExtensionList->getRawExtensions();

    $files_by_name = [];
    foreach ($extensions as $name => $extension) {
      $files_by_name[$name] = $extension->getPathname();
    }

    $info_by_file = $this->filesToInfo->filesGetInfoArrays($files_by_name);

    foreach ($extensions as $name => $extension) {
      $file = $files_by_name[$name];
      if (!array_key_exists($file, $info_by_file)) {
        throw new \RuntimeException("No info array found for '$file'.");
      }
      $extension->info = $info_by_file[$file] + $this->defaults;
    }

    return $extensions;
  }
}
