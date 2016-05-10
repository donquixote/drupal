<?php

namespace Drupal\Core\Extension\List_\Builder;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\FilesToInfo\FilesToInfo_InfoParser;
use Drupal\Core\Extension\FilesToInfo\FilesToInfoInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\List_\ExtensionList_Buffer;
use Drupal\Core\Extension\List_\ExtensionList_Cache;
use Drupal\Core\Extension\List_\ExtensionList_FromRawExtensionList;
use Drupal\Core\Extension\List_\ExtensionList_MultipleProcessorDecorator;
use Drupal\Core\Extension\List_\ExtensionListUtil;
use Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_AddMtime;
use Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_Dependencies;
use Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_InstalledWeightsFromConfigFactory;
use Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_InstalledWeightsStatic;
use Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_ProfileAsModule;
use Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_RequiredDependencies;
use Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_SystemInfoAlter;
use Drupal\Core\Extension\List_\Raw\RawExtensionList_FilesByName;
use Drupal\Core\Extension\List_\Raw\RawExtensionList_ProfileAsModule;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ProfileName\ProfileName_DrupalGetProfile;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\ProfileName\ProfileNameInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

class ExtensionListBuilder {

  /**
   * @var string
   */
  private $root;

  /**
   * @var array
   */
  private $filesByNameProviders;

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
   * @param string $root
   * @param array $filesByNameProviders
   *
   * @return self
   */
  public static function create($root, array $filesByNameProviders) {
    return new self($root, $filesByNameProviders);
  }

  /**
   * @param string $root
   * @param array $filesByNameProviders
   */
  public function __construct($root, array $filesByNameProviders) {
    $this->root = $root;
    $this->filesByNameProviders = $filesByNameProviders;
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
   * @return \Drupal\Core\Extension\List_\ExtensionListingInterface[]
   *   Format: $[$extension_type] = $extension_list
   */
  public function buildAll() {
    
    $raw_lists = [];
    foreach ($this->filesByNameProviders as $type => $filesByNameProvider) {
      $raw_lists[$type] = RawExtensionList_FilesByName::create($filesByNameProvider, $this->root, $type);
    }

    // Active profile is also listed as a module.
    $raw_lists['module'] = new RawExtensionList_ProfileAsModule($raw_lists['module'], $raw_lists['profile'], $this->activeProfileNameProvider);

    $filesToInfo = $this->filesToInfo === NULL
      ? FilesToInfo_InfoParser::create()
      : $this->filesToInfo;

    $lists = [];
    foreach ($raw_lists as $type => $raw_list) {
      $extension_list = new ExtensionList_FromRawExtensionList($raw_list, $filesToInfo, ExtensionListUtil::typeGetDefaults($type));

      $processors = $this->typeBuildProcessors($type);
      if ([] !== $processors) {
        $extension_list = new ExtensionList_MultipleProcessorDecorator($extension_list, $processors);
      }

      if ($this->useBuffer) {
        $extension_list = new ExtensionList_Buffer($extension_list);
      }

      if ($this->cache !== NULL && $this->cacheId !== NULL) {
        $extension_list = new ExtensionList_Cache($extension_list, $this->cache, $this->cacheId);
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
      $processors[] = new ExtensionListProcessor_AddMtime();
    }
    if ($this->systemInfoAlterModuleHandler !== NULL) {
      $processors[] = new ExtensionListProcessor_SystemInfoAlter($this->systemInfoAlterModuleHandler, $type);
    }
    if ($type === 'module') {
      $processors[] = new ExtensionListProcessor_ProfileAsModule($this->activeProfileNameProvider);
      $processors[] = new ExtensionListProcessor_RequiredDependencies($this->translationService);
      if ($this->installedWeightsConfigFactory !== NULL) {
        $processors[] = new ExtensionListProcessor_InstalledWeightsFromConfigFactory($this->installedWeightsConfigFactory, $type);
      }
      elseif (isset($this->installedWeightsByType[$type])) {
        $processors[] = new ExtensionListProcessor_InstalledWeightsStatic($this->installedWeightsByType[$type]);
      }
      $processors[] = new ExtensionListProcessor_Dependencies();
    }

    return $processors;
  }

}
