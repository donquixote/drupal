<?php
namespace Drupal\Core\Extension\Hub;

use Drupal\Core\Extension\FilenameList\ExtensionFilenameListInterface;
use Drupal\Core\Extension\InfoList\ExtensionInfoListInterface;
use Drupal\Core\Extension\List_\ExtensionListInterface;

/**
 * Provides various information about available extensions, for a specific
 * extension type (e.g. 'module', 'theme' or 'profile').
 */
interface ExtensionHubInterface extends ExtensionListInterface, ExtensionFilenameListInterface, ExtensionInfoListInterface {

  /**
   * Resets any stored or buffered data.
   *
   * @return $this
   */
  public function reset();

  /**
   * Determines if an extension exists in the filesystem.
   *
   * @param string $name
   *   The machine name of the extension.
   *
   * @return bool
   *   True if the extension exists (whether installed or not) and false if not.
   */
  public function extensionExists($name);

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
  public function nameGetLabel($machine_name);

  /**
   * Returns a single extension.
   *
   * @param string $name
   *   The extension machine name.
   *
   * @return \Drupal\Core\Extension\Extension
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied name.
   */
  public function nameGetExtension($name);

  /**
   * Returns information about a specified extension.
   *
   * This function returns the contents of the .info.yml file for the specified
   * installed extension.
   *
   * @param string $extension_name
   *   The name of an extension whose information shall be returned. If an
   *   extension with this name does not exist, an exception is thrown.
   *
   * @return mixed[]
   *   An associative array of extension information.
   *
   * @throws \InvalidArgumentException
   *   If there is no extension with the supplied name.
   */
  public function nameGetInfo($extension_name);

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
  public function nameSetFilename($extension_name, $filename);

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
  public function nameGetFilename($extension_name);

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
  public function nameGetPath($extension_name);
}
