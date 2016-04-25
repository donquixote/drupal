<?php

namespace Drupal\Core\Extension\List_;

use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

abstract class DiscoveryExtensionListBase implements ExtensionListInterface {

  /**
   * The type of the extension, such as "module" or "theme".
   *
   * @var string
   */
  private $type;

  /**
   * The info parser.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  private $infoParser;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The extension discovery service.
   *
   * @var \Drupal\Core\Extension\ExtensionDiscovery
   */
  protected $extensionDiscovery;

  /**
   * Default values to be merged into *.info.yml file arrays.
   *
   * @var mixed[]
   */
  private $defaults;

  /**
   * Constructs a new DiscoveryExtensionListSource instance.
   *
   * @param string $type
   *   The extension type.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ExtensionDiscovery $extension_discovery
   *   The extension discovery object.
   * @param array $info_defaults
   *   Defaults to merge into $extension->info.
   */
  public function __construct(
    $type,
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler,
    ExtensionDiscovery $extension_discovery,
    array $info_defaults
  ) {
    $this->type = $type;
    $this->infoParser = $info_parser;
    $this->moduleHandler = $module_handler;
    $this->extensionDiscovery = $extension_discovery;
    $this->defaults = $info_defaults;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    // Nothing to do.
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
   * Returns all available extensions.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *
   * @throws \Drupal\Core\Extension\InfoParserException
   *   If one of the .info.yml is broken or incomplete.
   */
  public function listExtensions() {
    // Find extensions.
    $extensions = $this->doScanExtensions();

    // Read info files for each extension.
    foreach ($extensions as $name => $extension) {
      // @todo Clone the extension object, to prevent side effects?
      
      // Look for the info file.
      $extension->info = $this->infoParser->parse($extension->getPathname());

      // Add the info file modification time, so it becomes available for
      // contributed extensions to use for ordering extension lists.
      /** @noinspection PhpUndefinedMethodInspection */
      $extension->info['mtime'] = $extension->getMTime();

      // Merge in defaults and save.
      $extension->info += $this->defaults;

      // Invoke hook_system_info_alter() to give installed modules a chance to
      // modify the data in the .info.yml files if necessary.
      $this->moduleHandler->alter('system_info', $extensions[$name]->info, $extensions[$name], $this->type);
    }

    return $extensions;
  }
}
