<?php

namespace Drupal\Core\Extension\ExtensionsByName\Builder;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ExtensionsByName\ExtensionsByName_Buffer;
use Drupal\Core\Extension\ExtensionsByName\ExtensionsByName_Cache;
use Drupal\Core\Extension\ExtensionsByName\ExtensionsByName_FromRawExtensionsByType;
use Drupal\Core\Extension\ExtensionsByName\ExtensionsByName_MultipleProcessorDecorator;
use Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameUtil;
use Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessor_AddMtime;
use Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessor_Dependencies;
use Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessor_InstalledWeightsFromConfigFactory;
use Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessor_InstalledWeightsStatic;
use Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessor_ProfileAsModule;
use Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessor_RequiredDependencies;
use Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessor_SystemInfoAlter;
use Drupal\Core\Extension\FilesToInfo\FilesToInfo_InfoParser;
use Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ProfileName\ProfileName_DrupalGetProfile;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\ProfileName\ProfileNameInterface;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByType_ActiveProfileAsModule;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByType_Buffer;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

class ExtensionsByNameBuilder {

  /**
   * @var \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface
   */
  private $rawExtensionsByType;

  /**
   * @var \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface
   */
  private $filesToInfo;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|null
   */
  private $systemInfoAlterModuleHandler;

  /**
   * @var bool
   */
  private $addMtime = TRUE;

  /**
   * @var \Drupal\Core\StringTranslation\TranslationInterface|null
   */
  private $translationService;

  /**
   * @var string
   */
  private $activeProfileNameProvider;

  /**
   * @var int[][]
   *   Format: $['module']['block'] = 3
   */
  private $installedWeightsByType = [];

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface|null
   */
  private $installedWeightsConfigFactory;

  /**
   * @var bool
   */
  private $useBuffer = FALSE;

  /**
   * @var \Drupal\Core\Cache\CacheBackendInterface|null
   */
  private $cache;

  /**
   * @var string|null
   */
  private $cacheId;

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $rawExtensionsByType
   *
   * @return \Drupal\Core\Extension\ExtensionsByName\Builder\ExtensionsByNameBuilder
   */
  public static function create(RawExtensionsByTypeInterface $rawExtensionsByType) {
    return new self($rawExtensionsByType);
  }

  /**
   * @param \Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByTypeInterface $rawExtensionsByType
   */
  public function __construct(RawExtensionsByTypeInterface $rawExtensionsByType) {
    $this->rawExtensionsByType = $rawExtensionsByType;
    $this->activeProfileNameProvider = new ProfileName_DrupalGetProfile();
  }

  /**
   * @param \Drupal\Core\Extension\InfoParserInterface $infoParser
   *
   * @return static
   */
  public function withInfoParser(InfoParserInterface $infoParser) {
    return $this->withFilesToInfo(new FilesToInfo_InfoParser($infoParser));
  }

  /**
   * @param \Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface $filesToInfo
   *
   * @return static
   */
  public function withFilesToInfo(FilesToInfoInterface $filesToInfo) {
    $clone = clone $this;
    $clone->filesToInfo = $filesToInfo;
    return $clone;
  }

  /**
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *
   * @return static
   */
  public function withSystemInfoAlter(ModuleHandlerInterface $moduleHandler) {
    $clone = clone $this;
    $clone->systemInfoAlterModuleHandler = $moduleHandler;
    return $clone;
  }

  /**
   * @return static
   */
  public function withoutMtime() {
    $clone = clone $this;
    $clone->addMtime = FALSE;
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
   * @param int[][] $installed_weights_by_type
   *   Format: $['module']['block'] = 3
   *
   * @return static
   */
  public function withInstalledWeightsStatic(array $installed_weights_by_type) {
    $clone = clone $this;
    $clone->installedWeightsByType = $installed_weights_by_type;
    return $clone;
  }

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *
   * @return static
   */
  public function withInstalledWeightsFromConfigFactory(ConfigFactoryInterface $configFactory) {
    $clone = clone $this;
    $clone->installedWeightsConfigFactory = $configFactory;
    return $clone;
  }

  /**
   * @return static
   */
  public function withBuffer() {
    $clone = clone $this;
    $clone->useBuffer = TRUE;
    return $clone;
  }

  /**
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   * @param string $cache_id
   *
   * @return static
   */
  public function withCache(CacheBackendInterface $cache, $cache_id) {
    $clone = clone $this;
    $clone->cache = $cache;
    $clone->cacheId = $cache_id;
    return $clone;
  }

  /**
   * Creates one extension list per type.
   *
   * @return \Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameInterface[]
   *   Format: $[$extension_type] = $extension_list
   */
  public function buildAll() {

    $rawExtensionsByType = $this->rawExtensionsByType;
    $rawExtensionsByType = new RawExtensionsByType_ActiveProfileAsModule($rawExtensionsByType, $this->activeProfileNameProvider);
    $rawExtensionsByType = new RawExtensionsByType_Buffer($rawExtensionsByType);

    $filesToInfo = $this->filesToInfo === NULL
      ? FilesToInfo_InfoParser::create()
      : $this->filesToInfo;

    $lists = [];
    foreach (['profile', 'module', 'theme', 'theme_engine'] as $type) {
      $defaults = ExtensionsByNameUtil::typeGetDefaults($type);
      $extension_list = new ExtensionsByName_FromRawExtensionsByType($rawExtensionsByType, $type, $filesToInfo, $defaults);

      $processors = $this->typeBuildProcessors($type);
      if ([] !== $processors) {
        $extension_list = new ExtensionsByName_MultipleProcessorDecorator($extension_list, $processors);
      }

      if ($this->useBuffer) {
        $extension_list = new ExtensionsByName_Buffer($extension_list);
      }

      if ($this->cache !== NULL && $this->cacheId !== NULL) {
        $extension_list = new ExtensionsByName_Cache($extension_list, $this->cache, $this->cacheId);
      }

      $lists[$type] = $extension_list;
    }

    return $lists;
  }

  /**
   * @param string $type
   *
   * @return array
   */
  private function typeBuildProcessors($type) {

    $processors = [];
    if ($this->addMtime) {
      $processors[] = new ExtensionsProcessor_AddMtime();
    }
    if ($this->systemInfoAlterModuleHandler !== NULL) {
      $processors[] = new ExtensionsProcessor_SystemInfoAlter($this->systemInfoAlterModuleHandler, $type);
    }
    if ($type === 'module') {
      $processors[] = new ExtensionsProcessor_ProfileAsModule($this->activeProfileNameProvider);
      $processors[] = new ExtensionsProcessor_RequiredDependencies($this->translationService);
      if ($this->installedWeightsConfigFactory !== NULL) {
        $processors[] = new ExtensionsProcessor_InstalledWeightsFromConfigFactory($this->installedWeightsConfigFactory, $type);
      }
      elseif (isset($this->installedWeightsByType[$type])) {
        $processors[] = new ExtensionsProcessor_InstalledWeightsStatic($this->installedWeightsByType[$type]);
      }
      $processors[] = new ExtensionsProcessor_Dependencies();
    }

    return $processors;
  }

}
