<?php


namespace Drupal\Core\Site;


class SiteInitState {

  /**
   * The absolute path to the Drupal root directory.
   *
   * @var string
   */
  private $root;

  /**
   * Whether the Site singleton was instantiated by the installer.
   *
   * @var bool
   */
  private $isInstallationProcess = FALSE;

  /**
   * @var \Drupal\Core\Site\SiteDirectory|null
   */
  private $siteDirectory;

  /**
   * Constructs the site state object.
   *
   * @param string $root_directory
   * @param bool $is_installer
   */
  public function __construct($root_directory, $is_installer) {
    $this->root = $root_directory;
    $this->isInstallationProcess = $is_installer;
  }

  /**
   * @return bool
   */
  public function isInstaller() {
    return $this->isInstallationProcess;
  }

  /**
   * Initializes the site path.
   *
   * @param array|null $sites
   *   (optional) A multi-site mapping, as defined in settings.php,
   *   or NULL if no multi-site functionality is enabled.
   * @param string $custom_path
   *   (optional) An explicit site path to set; skipping site negotiation.
   *
   * @throws \BadMethodCallException
   */
  public function initializePath(array $sites = NULL, $custom_path = NULL) {
    if (isset($this->siteDirectory)) {
      throw new \BadMethodCallException('Site path is already initialized.');
    }
    // Force-override the site directory in tests.
    if ($test_prefix = drupal_valid_test_ua()) {
      $path = 'sites/simpletest/' . substr($test_prefix, 10);
    }
    // An explicitly defined $conf_path in /settings.php takes precedence.
    elseif (isset($custom_path)) {
      $path = $custom_path;
    }
    // If the multi-site functionality was enabled in /settings.php, discover
    // the path for the current site.
    // $sites just needs to be defined; an explicit mapping is not required.
    elseif (isset($sites)) {
      $site_picker = SitePicker::createFromEnvironment();
      $path = $site_picker->discoverPath($this->root, $sites, !$this->isInstallationProcess);
    }
    // If the multi-site functionality is not enabled, the Drupal root
    // directory is the site directory.
    else {
      $path = '';
    }
    $this->siteDirectory = new SiteDirectory($this->root, $path);
  }

  /**
   * @throws \RuntimeException
   * @return \Drupal\Core\Site\SiteDirectory
   */
  public function requireSiteDirectory() {
    // Extra safety protection in case a script somehow manages to bypass all
    // other protections.
    if (!isset($this->siteDirectory)) {
      throw new \RuntimeException('Site path is not initialized yet.');
    }
    return $this->siteDirectory;
  }

} 
