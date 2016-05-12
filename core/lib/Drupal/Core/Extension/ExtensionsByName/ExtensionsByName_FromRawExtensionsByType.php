<?php

namespace Drupal\Core\Extension\ExtensionsByName;

use Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface;

class ExtensionsByName_FromRawExtensionsByType implements ExtensionsByNameInterface {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  private $rawExtensionsByType;

  /**
   * @var string
   */
  private $type;

  /**
   * @var \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface
   */
  private $filesToInfo;

  /**
   * @var array
   */
  private $defaults;

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $rawExtensionsByType
   * @param string $type
   * @param \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface $filesToInfo
   * @param array $defaults
   *   Defaults to be merged into the info array parsed from the *.info.yml file.
   */
  public function __construct(RawExtensionsByTypeInterface $rawExtensionsByType, $type, FilesToInfoInterface $filesToInfo, array $defaults) {
    $this->rawExtensionsByType = $rawExtensionsByType;
    $this->type = $type;
    $this->filesToInfo = $filesToInfo;
    $this->defaults = $defaults;
  }

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset() {
    // @todo Reset $this->rawExtensionsByType
  }

  /**
   * Returns all available extensions, with $extension->info filled in.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getExtensions() {

    /**
     * @var \Drupal\Core\Extension\Extension[][] $extensions_by_type
     *   Format: $[$extension_type][$extension_name] = $extension
     */
    $extensions_by_type = $this->rawExtensionsByType->getRawExtensionsByType();
    if (!array_key_exists($this->type, $extensions_by_type)) {
      return [];
    }
    $extensions = $extensions_by_type[$this->type];

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
