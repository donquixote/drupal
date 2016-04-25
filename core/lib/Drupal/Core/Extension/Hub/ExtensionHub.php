<?php

namespace Drupal\Core\Extension\Hub;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\FilenameList\ExtensionFilenameList;
use Drupal\Core\Extension\FilenameList\ExtensionFilenameListBuffer;
use Drupal\Core\Extension\FilenameList\ExtensionFilenameListCache;
use Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface;
use Drupal\Core\Extension\InfoList\ExtensionInfoList;
use Drupal\Core\Extension\InfoList\ExtensionInfoListBuffer;
use Drupal\Core\Extension\InfoList\ExtensionInfoListCache;
use Drupal\Core\Extension\InfoList\ExtensionInfoListInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\List_\ExtensionListBuffer;
use Drupal\Core\Extension\List_\ExtensionListCache;
use Drupal\Core\Extension\List_\ExtensionListInterface;
use Drupal\Core\Extension\List_\ModuleDiscoveryExtensionList;
use Drupal\Core\Extension\List_\ProfileDiscoveryExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Default implementation for ExtensionHubInterface.
 */
class ExtensionHub implements ExtensionHubInterface {

  /**
   * The type of the extension, such as "module" or "theme".
   *
   * @var string
   */
  private $type;

  /**
   * @var \Drupal\Core\Extension\List_\ExtensionListInterface
   */
  private $source;

  /**
   * @var \Drupal\Core\Extension\InfoList\ExtensionInfoListInterface
   */
  private $infoList;

  /**
   * @var \Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface
   */
  private $fileList;

  /**
   * A list of extension file names directly added in code (not discovered).
   *
   * It is important to keep a separate list to ensure that it takes priority
   * over the discovered extension folders.
   *
   * @var string[]
   */
  private $addedFileNames = [];

  /**
   * @param string $root
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Extension\List_\ExtensionListInterface $profile_list
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *
   * @return \Drupal\Core\Extension\Hub\ExtensionHubInterface
   */
  static function createForModules(
    $root,
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler,
    ConfigFactoryInterface $config_factory,
    ExtensionListInterface $profile_list,
    CacheBackendInterface $cache
  ) {
    return self::create(
      'module',
      ModuleDiscoveryExtensionList::create(
        $info_parser,
        $module_handler,
        new ExtensionDiscovery($root),
        $config_factory,
        $profile_list),
      $cache);
  }

  /**
   * @param $root
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *
   * @return \Drupal\Core\Extension\Hub\ExtensionHubInterface
   */
  static function createForProfiles(
    $root,
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler,
    CacheBackendInterface $cache
  ) {
    return self::create(
      'profile',
      ProfileDiscoveryExtensionList::create(
        $info_parser,
        $module_handler,
        new ExtensionDiscovery($root)),
      $cache);
  }

  /**
   * @param string $type
   * @param \Drupal\Core\Extension\List_\ExtensionListInterface $extension_list
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *
   * @return \Drupal\Core\Extension\Hub\ExtensionHubInterface
   */
  static function create(
    $type,
    ExtensionListInterface $extension_list,
    CacheBackendInterface $cache
  ) {

    $extension_list = new ExtensionListCache($extension_list, $cache, 'core.extension_listing.' . $type);
    $extension_list = new ExtensionListBuffer($extension_list);

    $info_list = new ExtensionInfoList($extension_list);
    $info_list = new ExtensionInfoListCache($info_list, $cache, "system.$type.info");
    $info_list = new ExtensionInfoListBuffer($info_list);

    $file_list = new ExtensionFilenameList($extension_list);
    $file_list = new ExtensionFilenameListCache($file_list, $cache, "system.$type.files");
    $file_list = new ExtensionFilenameListBuffer($file_list);

    return new self($type, $extension_list, $info_list, $file_list);
  }

  /**
   * @param string $type
   * @param \Drupal\Core\Extension\List_\ExtensionListInterface $source
   * @param \Drupal\Core\Extension\InfoList\ExtensionInfoListInterface $info_list
   * @param \Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface $file_list
   */
  function __construct(
    $type,
    ExtensionListInterface $source,
    ExtensionInfoListInterface $info_list,
    ExtensionFilenameListInterface $file_list
  ) {
    $this->type = $type;
    $this->source = $source;
    $this->infoList = $info_list;
    $this->fileList = $file_list;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->source->reset();
    $this->infoList->reset();
    $this->fileList->reset();
    $this->addedFileNames = NULL;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function extensionExists($name) {
    $extensions = $this->listExtensions();
    return isset($extensions[$name]);
  }

  /**
   * {@inheritdoc}
   */
  public function nameGetLabel($machine_name) {
    return $this->nameGetExtension($machine_name)->info['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function nameGetExtension($name) {
    $extensions = $this->listExtensions();
    if (isset($extensions[$name])) {
      return $extensions[$name];
    }

    throw new \InvalidArgumentException("The {$this->type} $name does not exist.");
  }

  /**
   * {@inheritdoc}
   */
  public function listExtensions() {
    return $this->source->listExtensions();
  }

  /**
   * {@inheritdoc}
   */
  public function nameGetInfo($extension_name) {
    // Ensure that $this->extensionInfo is primed.
    $allInfo = $this->getAllInfo();
    if (isset($allInfo[$extension_name])) {
      return $allInfo[$extension_name];
    }
    throw new \InvalidArgumentException("The {$this->type} $extension_name does not exist.");
  }

  /**
   * {@inheritdoc}
   */
  public function getAllInfo() {
    return $this->infoList->getAllInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function getFilenames() {
    return $this->fileList->getFilenames();
  }

  /**
   * {@inheritdoc}
   */
  public function nameSetFilename($extension_name, $filename) {
    $this->addedFileNames[$extension_name] = $filename;
  }

  /**
   * {@inheritdoc}
   */
  public function nameGetFilename($extension_name) {
    if (isset($this->addedFileNames[$extension_name])) {
      return $this->addedFileNames[$extension_name];
    }
    $filenames = $this->getFilenames();
    if (isset($filenames[$extension_name])) {
      return $filenames[$extension_name];
    }
    throw new \InvalidArgumentException("The {$this->type} $extension_name does not exist.");
  }

  /**
   * {@inheritdoc}
   */
  public function nameGetPath($extension_name) {
    return dirname($this->nameGetFilename($extension_name));
  }
}
