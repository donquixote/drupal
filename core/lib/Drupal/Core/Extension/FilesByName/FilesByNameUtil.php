<?php

namespace Drupal\Core\Extension\FilesByName;

use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Regex;
use Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface;
use Drupal\Core\Extension\ProfileDirs\ProfileDirs_Active;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\ProfileName\ProfileNameInterface;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGrouped_Buffer;
use Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGrouped_Common;
use Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface;
use Drupal\Core\Extension\SearchdirToFiles\SearchdirToFiles_Buffer;
use Drupal\Core\Extension\SearchdirToFiles\SearchdirToFiles_Readdir;
use Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface;
use Drupal\Core\Extension\SitePath\SitePath_Static;
use Drupal\Core\Extension\SitePath\SitePathInterface;

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
    $sitePathProvider = new SitePath_Static($site_path);
    $searchdirToFiles = SearchdirToFiles_Readdir::create($root);
    if ($use_buffers) {
      $searchdirToFiles = new SearchdirToFiles_Buffer($searchdirToFiles);
    }
    $filesToTypes = new FilesToTypes_Regex($root);
    $activeProfileNameProvider = new ProfileName_Static($active_profile_name);
    return self::createAllFromBaseComponents($sitePathProvider, $searchdirToFiles, $filesToTypes, $activeProfileNameProvider, $use_buffers);
  }

  /**
   * @param \Drupal\Core\Extension\SitePath\SitePathInterface $sitePathProvider
   * @param \Drupal\Core\Extension\SearchdirToFiles\SearchdirToFilesInterface $searchdirToFiles
   * @param \Drupal\Core\Extension\FilesToTypes\FilesToTypesInterface $filesToTypes
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $activeProfileNameProvider
   * @param bool $use_buffers
   *
   * @return \Drupal\Core\Extension\FilesByName\FilesByNameInterface[]
   */
  public static function createAllFromBaseComponents(
    SitePathInterface $sitePathProvider,
    SearchdirToFilesInterface $searchdirToFiles,
    FilesToTypesInterface $filesToTypes,
    ProfileNameInterface $activeProfileNameProvider,
    $use_buffers = TRUE
  ) {
    $searchdirPrefixes = new SearchdirPrefixes_Common($sitePathProvider);
    $searchdirPrefixToFilesGrouped = SearchdirPrefixToFilesGrouped_Common::createFromComponents($searchdirToFiles, $filesToTypes);
    if ($use_buffers) {
      $searchdirPrefixToFilesGrouped = new SearchdirPrefixToFilesGrouped_Buffer($searchdirPrefixToFilesGrouped);
    }
    return self::createAllFromComponents($searchdirPrefixes, $searchdirPrefixToFilesGrouped, $activeProfileNameProvider, $use_buffers);
  }

  /**
   * Creates a simple version, without intermediate cache or buffer layers.
   *
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixes
   * @param \Drupal\Core\Extension\SearchdirPrefixToFilesGrouped\SearchdirPrefixToFilesGroupedInterface $searchdirPrefixToFilesGrouped
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $activeProfileNameProvider
   *
   * @param bool $use_buffers
   *
   * @return \Drupal\Core\Extension\FilesByName\FilesByNameInterface[]
   *   Format: $[$extension_type] = $filesByNameProvider.
   */
  public static function createAllFromComponents(
    SearchdirPrefixesInterface $searchdirPrefixes,
    SearchdirPrefixToFilesGroupedInterface $searchdirPrefixToFilesGrouped,
    ProfileNameInterface $activeProfileNameProvider,
    $use_buffers = TRUE
  ) {
    $providersByType = [];

    $provider = new FilesByName_Profile($searchdirPrefixes, $searchdirPrefixToFilesGrouped);
    if ($use_buffers) {
      $provider = new FilesByName_Buffer($provider);
    }
    $providersByType['profile'] = $provider;
    $profileDirs = new ProfileDirs_Active($providersByType['profile'], $activeProfileNameProvider);

    foreach (['module', 'theme', 'theme_engine'] as $type) {
      $provider = new FilesByName_NonProfile($searchdirPrefixes, $searchdirPrefixToFilesGrouped, $type, $profileDirs);
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
