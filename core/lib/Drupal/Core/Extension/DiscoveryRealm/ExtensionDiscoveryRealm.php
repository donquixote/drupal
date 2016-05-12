<?php

namespace Drupal\Core\Extension\DiscoveryRealm;

use Drupal\Core\Extension\FilesByType\FilesByType_FromFilesGrouped;
use Drupal\Core\Extension\ProfileName\ProfileName_DrupalGetProfile;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\ProfileName\ProfileNameInterface;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByType_FromRawExtensionsGrouped;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Fixed;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedSingleton;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedSingleton;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslationInterface;

class ExtensionDiscoveryRealm {

  /**
   * @var string|null
   */
  private $root;

  /**
   * @var bool|null
   */
  private $includeTests;

  /**
   * @var \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface|null
   */
  private $searchdirToRawExtensionsGrouped;

  /**
   * @var \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface|null
   */
  private $searchdirPrefixesProvider;

  /**
   * @var \Drupal\Core\StringTranslation\TranslationInterface|null
   */
  private $translationService;

  /**
   * @var \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  private $activeProfileNameProvider;

  /**
   * @param string|null $root
   * @param bool|null $include_tests
   */
  public function __construct($root = NULL, $include_tests = NULL) {
    $this->root = $root;
    $this->includeTests = $include_tests;
  }

  /**
   * @param string|null $root
   * @param bool|null $include_tests
   *
   * @return \Drupal\Core\Extension\DiscoveryRealm\ExtensionDiscoveryRealm
   */
  public static function build($root = NULL, $include_tests = NULL) {
    return new ExtensionDiscoveryRealm($root, $include_tests);
  }

  /**
   * @param string $root
   *
   * @return static
   */
  public function withRootPath($root) {
    $clone = clone $this;
    $clone->root = $root;
    return $clone;
  }

  /**
   * @param bool $include_tests
   *
   * @return static
   */
  public function withTestDirsIncluded($include_tests = TRUE) {
    $clone = clone $this;
    $clone->includeTests = $include_tests;
    return $clone;
  }

  /**
   * @param \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped
   *
   * @return static
   */
  public function withSearchdirToRawExtensionsGrouped(SearchdirToRawExtensionsGroupedInterface $searchdirToRawExtensionsGrouped) {
    $clone = clone $this;
    $clone->searchdirToRawExtensionsGrouped = $searchdirToRawExtensionsGrouped;
    return $clone;
  }

  /**
   * @param string $site_path
   *   The site path, e.g. 'sites/default'.
   *   If the site path is NULL, one will be automatically determined from
   *   global state.
   * @param bool $support_simpletest
   *   TRUE to add an additional search directory for simpletest.
   *
   * @return static
   */
  public function withSearchdirsSitePath($site_path, $support_simpletest = TRUE) {
    return $this->withSearchdirPrefixesProvider(new SearchdirPrefixes_Common($site_path, $support_simpletest));
  }

  /**
   * @param int[] $searchdir_prefix_weights
   *
   * @return static
   */
  public function withSearchdirPrefixWeights($searchdir_prefix_weights) {
    return $this->withSearchdirPrefixesProvider(new SearchdirPrefixes_Fixed($searchdir_prefix_weights));
  }

  /**
   * @param \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface $searchdirPrefixesProvider
   *
   * @return static
   */
  public function withSearchdirPrefixesProvider(SearchdirPrefixesInterface $searchdirPrefixesProvider) {
    $clone = clone $this;
    unset($clone->sitePath);
    $clone->searchdirPrefixesProvider = $searchdirPrefixesProvider;
    return $clone;
  }

  /**
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translationService
   *
   * @return static
   */
  public function withTranslationService(TranslationInterface $translationService) {
    $clone = clone $this;
    $clone->translationService = $translationService;
    return $clone;
  }

  /**
   * @param string $active_profile_name
   *
   * @return static
   */
  public function withActiveProfileName($active_profile_name) {
    return $this->withActiveProfileNameProvider(new ProfileName_Static($active_profile_name));
  }

  /**
   * @param \Drupal\Core\Extension\ProfileName\ProfileNameInterface $activeProfileNameProvider
   *
   * @return static
   */
  public function withActiveProfileNameProvider(ProfileNameInterface $activeProfileNameProvider) {
    $clone = clone $this;
    $clone->activeProfileNameProvider = $activeProfileNameProvider;
    return $clone;
  }

  /**
   * @param string $type
   *
   * @return string[]
   */
  public function typeGetMachineNames($type) {
    return array_keys($this->typeGetFiles($type));
  }

  /**
   * @param string $type
   *   E.g. 'module' or 'theme_engine'.
   *
   * @return string[]
   *   Format: $[$extension_name] = $extension_info_file
   *   E.g. $['system'] = 'core/modules/system/system.info.yml'
   */
  public function typeGetFiles($type) {
    $files_by_type = $this->getFilesByTypeProvider()->getFilesByType();
    return array_key_exists($type, $files_by_type)
      ? $files_by_type[$type]
      : [];
  }

  /**
   * @return \Drupal\Core\Extension\FilesByType\FilesByTypeInterface
   */
  public function getFilesByTypeProvider() {
    return FilesByType_FromFilesGrouped::create(
      $this->getSearchdirPrefixesProvider(),
      $this->getSearchdirToFilesGrouped(),
      $this->getProfileNameProvider());
  }

  /**
   * @return \Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGroupedInterface
   */
  public function getSearchdirToFilesGrouped() {
    return SearchdirToFilesGroupedSingleton::getInstance($this->getRootPath(), $this->getIncludeTests());
  }

  /**
   * @return \Drupal\Core\Extension\Extension[]
   *   Format: $[$extension_name] = $extension
   */
  public function getRawProfiles() {
    return $this->typeGetRawExtensions('profile');
  }

  /**
   * @return \Drupal\Core\Extension\Extension[]
   *   Format: $[$extension_name] = $extension
   */
  public function getRawModules() {
    return $this->typeGetRawExtensions('module');
  }

  /**
   * @param string $type
   *   E.g. 'module' or 'theme_engine'.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   Format: $[$extension_name] = $extension
   */
  public function typeGetRawExtensions($type) {
    $raw_extensions_by_type = $this->getRawExtensionsByTypeProvider()->getRawExtensionsByType();
    return array_key_exists($type, $raw_extensions_by_type)
      ? $raw_extensions_by_type[$type]
      : [];
  }

  /**
   * @return \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  public function getRawExtensionsByTypeProvider() {
    return RawExtensionsByType_FromRawExtensionsGrouped::create(
      $this->getSearchdirPrefixesProvider(),
      $this->getSearchdirToRawExtensionsGrouped(),
      new ProfileName_DrupalGetProfile());
  }

  /**
   * @return \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixesInterface
   */
  public function getSearchdirPrefixesProvider() {
    if ($this->searchdirPrefixesProvider !== NULL) {
      return $this->searchdirPrefixesProvider;
    }
    return new SearchdirPrefixes_Common();
  }

  /**
   * @return \Drupal\Core\Extension\ProfileName\ProfileNameInterface
   */
  public function getProfileNameProvider() {
    if ($this->activeProfileNameProvider !== NULL) {
      return $this->activeProfileNameProvider;
    }
    return new ProfileName_DrupalGetProfile();
  }

  /**
   * @return \Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedInterface
   */
  public function getSearchdirToRawExtensionsGrouped() {
    if ($this->searchdirToRawExtensionsGrouped !== NULL) {
      return $this->searchdirToRawExtensionsGrouped;
    }
    return SearchdirToRawExtensionsGroupedSingleton::getInstance($this->getRootPath(), $this->getIncludeTests());
  }

  /**
   * @return string
   */
  public function getRootPath() {
    return $this->root !== NULL
      ? $this->root
      : \Drupal::root();
  }

  /**
   * @return bool
   */
  public function getIncludeTests() {
    if ($this->includeTests !== NULL) {
      return $this->includeTests;
    }
    else {
      return Settings::get('extension_discovery_scan_tests') || drupal_valid_test_ua();
    }
  }

}
