<?php

namespace Drupal\Core\Extension\FilesByName;

use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Regex;
use Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface;
use Drupal\Core\Extension\ProfileDirs\ProfileDirs_Active;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\ProfileName\ProfileNameInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGrouped_Buffer;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGrouped_Common;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_Buffer;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_Readdir;
use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface;

final class FilesByNameUtil {

  private function __construct() {}

  /**
   * @param string $root
   * @param string $site_path
   * @param string $active_profile_name
   * @param bool $use_buffers
   *
   * @return \Drupal\Core\Extension\FilesByName\FilesByNameInterface[]
   */
  public static function createAllFromFixedValues($root, $site_path, $active_profile_name, $use_buffers = TRUE) {
    $searchdirToFiles = DirectoryToFiles_Readdir::create($root);
    if ($use_buffers) {
      $searchdirToFiles = new DirectoryToFiles_Buffer($searchdirToFiles);
    }
    $filesToTypes = new FilesToTypes_Regex($root);
    $activeProfileNameProvider = new ProfileName_Static($active_profile_name);
    return self::createAllFromBaseComponents($site_path, $searchdirToFiles, $filesToTypes, $activeProfileNameProvider, $use_buffers);
  }

  /**
   * @param string $sitePath
   * @param \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface $searchdirToFiles
   * @param \Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface $filesToTypes
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $activeProfileNameProvider
   * @param bool $use_buffers
   *
   * @return \Drupal\Core\Extension\FilesByName\FilesByNameInterface[]
   */
  public static function createAllFromBaseComponents(
    $sitePath = NULL,
    DirectoryToFilesInterface $searchdirToFiles,
    FilesToTypesInterface $filesToTypes,
    ProfileNameInterface $activeProfileNameProvider,
    $use_buffers = TRUE
  ) {
    $searchdirPrefixes = new SearchdirPrefixes_Common($sitePath);
    $searchdirToFilesGrouped = SearchdirToFilesGrouped_Common::createFromComponents($searchdirToFiles, $filesToTypes);
    if ($use_buffers) {
      $searchdirToFilesGrouped = new SearchdirToFilesGrouped_Buffer($searchdirToFilesGrouped);
    }
    return self::createAllFromComponents($searchdirPrefixes, $searchdirToFilesGrouped, $activeProfileNameProvider, $use_buffers);
  }

  /**
   * Creates a simple version, without intermediate cache or buffer layers.
   *
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface $searchdirToFilesGrouped
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $activeProfileNameProvider
   *
   * @param bool $use_buffers
   *
   * @return \Drupal\Core\Extension\FilesByName\FilesByNameInterface[]
   *   Format: $[$extension_type] = $filesByNameProvider.
   */
  public static function createAllFromComponents(
    SearchdirPrefixesInterface $searchdirPrefixes,
    SearchdirToFilesGroupedInterface $searchdirToFilesGrouped,
    ProfileNameInterface $activeProfileNameProvider,
    $use_buffers = TRUE
  ) {
    $providersByType = [];

    $provider = new FilesByName_Profile($searchdirPrefixes, $searchdirToFilesGrouped);
    if ($use_buffers) {
      $provider = new FilesByName_Buffer($provider);
    }
    $providersByType['profile'] = $provider;
    $profileDirs = new ProfileDirs_Active($providersByType['profile'], $activeProfileNameProvider);

    foreach (['module', 'theme', 'theme_engine'] as $type) {
      $provider = new FilesByName_NonProfile($searchdirPrefixes, $searchdirToFilesGrouped, $type, $profileDirs);
      if ($use_buffers) {
        $provider = new FilesByName_Buffer($provider);
      }
      $providersByType[$type] = $provider;
    }

    return $providersByType;
  }

  /**
   * @param \Drupal\Core\Extension\FilesByName\FilesByNameInterface[] $providers
   *
   * @return string[]
   *   Format: $['module']['system'] = 'core/modules/system/system.info.yml'
   */
  public static function providersGetFilesByTypeAndName(array $providers) {
    $files_by_type_and_name = [];
    foreach ($providers as $type => $filesByNameProvider) {
      $files_by_type_and_name[$type] = $filesByNameProvider->getFilesByName();
    }
    return $files_by_type_and_name;
  }

}
