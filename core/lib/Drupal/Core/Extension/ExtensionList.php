<?php

namespace Drupal\Core\Extension;

use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Provides available extensions.
 *
 * The extension list is per extension type, like module theme and profile.
 */
abstract class ExtensionList implements ExtensionListInterface {

  /**
   * The type of the extension, such as "module" or "theme".
   *
   * @var string
   */
  protected $type;

  /**
   * The app root.
   *
   * @var string
   */
  protected $root;

  /**
   * The cache.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * Default values to be merged into *.info.yml file arrays.
   *
   * @var mixed[]
   */
  protected $defaults = [];

  /**
   * The info parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The statically cached extensions.
   *
   * @var \Drupal\Core\Extension\Extension[]|null
   */
  protected $extensions;

  /**
   * Static caching for extension info.
   *
   * @var array[]
   *   Keys are extension names, and values their info arrays (mixed[]).
   */
  protected $extensionInfo;

  /**
   * A list of extension folder names keyed by extension name.
   *
   * @var string[]
   */
  protected $fileNames;

  /**
   * A list of extension folder names directly added in code (not discovered).
   *
   * It is important to keep a separate list to ensure that it takes priority
   * over the discovered extension folders.
   *
   * @var string[]
   */
  protected $addedFileNames;

  /**
   * The extension discovery service.
   *
   * @var \Drupal\Core\Extension\ExtensionDiscovery
   */
  protected $extensionDiscovery;

  /**
   * Constructs a new ExtensionList instance.
   *
   * @param string $root
   *   The app root.
   * @param string $type
   *   The extension type.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    $root,
    $type,
    CacheBackendInterface $cache,
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler
  ) {
    $this->root = $root;
    $this->type = $type;
    $this->cache = $cache;
    $this->infoParser = $info_parser;
    $this->moduleHandler = $module_handler;
    $this->extensionDiscovery = $this->getExtensionDiscovery();
  }

  /**
   * Returns the extension discovery.
   *
   * @return \Drupal\Core\Extension\ExtensionDiscovery
   */
  protected function getExtensionDiscovery() {
    return new ExtensionDiscovery($this->root);
  }

  /**
   * Resets the stored extension list.
   */
  public function reset() {
    $this->extensions = NULL;
    $this->cache->delete($this->getListCacheId());
    $this->extensionInfo = NULL;
    $this->cache->delete($this->getInfoCacheId());
    $this->fileNames = NULL;
    $this->cache->delete($this->getFilenameCacheId());
    $this->addedFileNames = NULL;
    return $this;
  }

  /**
   * Returns the extension list cache ID.
   *
   * @return string
   */
  protected function getListCacheId() {
    return 'core.extension_listing.' . $this->type;
  }

  /**
   * Returns the extension info cache ID.
   *
   * @return string
   */
  protected function getInfoCacheId() {
    return "system.{$this->type}.info";
  }

  /**
   * Returns the extension filenames cache ID.
   *
   * @return string
   */
  protected function getFilenameCacheId() {
    return "system.{$this->type}.files";
  }

  /**
   * Determines if an extension exists in the filesystem.
   *
   * @param string $name
   *   The machine name of the extension.
   *
   * @return bool
   *   True if the extension exists (whether installed or not) and false if not.
   */
  public function extensionExists($name) {
    $extensions = $this->listExtensions();
    return isset($extensions[$name]);
  }

  /**
   * Returns the human readable name of the extension.
   *
   * @param string $machine_name
   *   The extension name.
   *
   * @return string
   *   The human readable name of the extension.
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied machine name.
   */
  public function nameGetLabel($machine_name) {
    return $this->nameGetExtension($machine_name)->info['name'];
  }

  /**
   * Returns a single extension.
   *
   * @param string $name
   *   The extension name.
   *
   * @return \Drupal\Core\Extension\Extension
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied name.
   */
  public function nameGetExtension($name) {
    $extensions = $this->listExtensions();
    if (isset($extensions[$name])) {
      return $extensions[$name];
    }

    throw new \InvalidArgumentException("The {$this->type} $name does not exist.");
  }

  /**
   * Returns all available extensions.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function listExtensions() {
    if (isset($this->extensions)) {
      return $this->extensions;
    }
    if ($cache = $this->cache->get($this->getListCacheId())) {
      $this->extensions = $cache->data;
      return $this->extensions;
    }
    $extensions = $this->doListExtensions();
    $this->cache->set($this->getListCacheId(), $extensions);
    $this->extensions = $extensions;
    return $this->extensions;
  }

  /**
   * Scans the available extensions.
   *
   * Overriding this method gives other code the chance to add additional
   * extensions to this raw listing.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  protected function doScanExtensions() {
    return $this->extensionDiscovery->scan($this->type);
  }

  /**
   * Build the actual list of extensions before caching it.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  protected function doListExtensions() {
    // Find extensions.
    $extensions = $this->doScanExtensions();

    // Read info files for each extension.
    foreach ($extensions as $name => $extension) {
      // Look for the info file.
      $extension->info = $this->infoParser->parse($extension->getPathname());

      // Add the info file modification time, so it becomes available for
      // contributed extensions to use for ordering extension lists.
      $extension->info['mtime'] = $extension->getMTime();

      // Merge in defaults and save.
      $extensions[$name]->info = $extension->info + $this->defaults;

      // Invoke hook_system_info_alter() to give installed modules a chance to
      // modify the data in the .info.yml files if necessary.
      $this->moduleHandler->alter('system_info', $extensions[$name]->info, $extensions[$name], $this->type);
    }

    return $extensions;
  }

  /**
   * Returns information about a specified extension.
   *
   * This function returns the contents of the .info.yml file for the specified
   * installed extension.
   *
   * @param string $extension_name
   *   The name of an extension whose information shall be returned. If
   *   $name does not exist or is not enabled, an empty array will be returned.
   *
   * @return mixed[]
   *   An associative array of extension information.
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied name.
   */
  public function nameGetInfo($extension_name) {
    // Ensure that $this->extensionInfo is primed.
    $this->getAllInfo();
    if (isset($this->extensionInfo[$extension_name])) {
      return $this->extensionInfo[$extension_name];
    }
    throw new \InvalidArgumentException("The {$this->type} $extension_name does not exist.");
  }

  /**
   * Returns an array of information about enabled modules or themes.
   *
   * This function returns the contents of the .info.yml file for each installed
   * extension.
   *
   * @return array[]
   *   An associative array of extension information keyed by name. If no
   *   records are available, an empty array is returned.
   */
  public function getAllInfo() {
    if (!isset($this->extensionInfo)) {
      $cache_key = "system.{$this->type}.info";
      if ($cache = $this->cache->get($cache_key)) {
        $info = $cache->data;
      }
      else {
        $info = $this->recalculateInfo();
        $this->cache->set($cache_key, $info);
      }
      $this->extensionInfo = $info;
    }
    return $this->extensionInfo;
  }

  /**
   * Generates the information from .info.yml files for extensions of this type.
   *
   * The information is placed in cache with the "system.{extension_type}.info"
   * key.
   *
   * @return array[]
   *   An array of arrays of .info.yml entries keyed by the extension name.
   */
  protected function recalculateInfo() {
    $info = [];
    foreach ($this->listExtensions() as $name => $extension) {
      $info[$name] = $extension->info;
    }
    return $info;
  }

  /**
   * Returns a list of extension folder names keyed by extension name.
   *
   * @return string[]
   */
  public function getFilenames() {
    if (!isset($this->fileNames)) {
      $cache_id = $this->getFilenameCacheId();
      if ($cache = $this->cache->get($cache_id)) {
        $file_names = $cache->data;
      }
      else {
        $file_names = $this->recalculateFilenames();
        $this->cache->set($cache_id, $file_names);
      }
      $this->fileNames = $file_names;
    }
    return $this->fileNames;
  }

  /**
   * Generates a sorted list of .info.yml file locations for all extensions.
   *
   * The information is placed in cache with the "system.{extension_type}.files"
   * key.
   *
   * @return string[]
   *   An array of .info.yml file locations keyed by the extension name.
   */
  protected function recalculateFilenames() {
    $file_names = [];
    $extensions = $this->listExtensions();
    ksort($extensions);
    foreach ($extensions as $name => $extension) {
      $file_names[$name] = $extension->getPathname();
    }
    return $file_names;
  }

  /**
   * Sets the filename for an extension.
   *
   * This method is used in the Drupal bootstrapping phase, when the extension
   * system is not fully initialized, to manually set locations of modules and
   * profiles needed to complete bootstrapping.
   *
   * It is not recommended to call this method except in those rare cases.
   *
   * @param string $extension_name
   *   The name of the extension for which the filename is requested.
   * @param string $filename
   *   The filename of the extension which is to be set explicitly rather
   *   than by consulting the dynamic extension listing.
   */
  public function nameSetFilename($extension_name, $filename) {
    $this->addedFileNames[$extension_name] = $filename;
  }

  /**
   * Gets the filename for a system resource.
   *
   * The filename, whether provided, cached, or retrieved from the database, is
   * only returned if the file exists.
   *
   * This function plays a key role in allowing Drupal's extensions (modules,
   * themes, profiles, theme_engines, etc.) to be located in different places
   * depending on a site's configuration. For example, a module 'foo' may
   * legally be located in any of these three places:
   *
   * core/modules/foo/foo.info.yml
   * modules/foo/foo.info.yml
   * sites/all/modules/foo/foo.info.yml
   * sites/example.com/modules/foo/foo.info.yml
   *
   * while a theme 'bar' may be located in any of similar places:
   *
   * core/themes/bar/bar.info.yml
   * themes/bar/bar.info.yml
   * sites/all/themes/bar/bar.info.yml
   * sites/example.com/themes/bar/bar.info.yml
   *
   * Calling ExtensionList::getFilename('foo') will give you one of the above,
   * depending on where the extension is located and what type it is.
   *
   * @param string $extension_name
   *   The name of the extension for which the filename is requested.
   *
   * @return string
   *   The filename of the requested extension's .info.yml file.
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied name.
   */
  public function nameGetFilename($extension_name) {
    // Ensure that $this->fileNames is primed.
    $this->getFilenames();
    if (isset($this->addedFileNames[$extension_name])) {
      return $this->addedFileNames[$extension_name];
    }
    elseif (isset($this->fileNames[$extension_name])) {
      return $this->fileNames[$extension_name];
    }
    throw new \InvalidArgumentException("The {$this->type} $extension_name does not exist.");
  }


  /**
   * Gets the path to an extension of a specific type (module, theme, etc.).
   *
   * @param string $extension_name
   *   The name of the extension for which the path is requested.
   *
   * @return string
   *   The drupal-root-relative path to the specified extension.
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied name.
   */
  public function nameGetPath($extension_name) {
    return dirname($this->nameGetFilename($extension_name));
  }

}
