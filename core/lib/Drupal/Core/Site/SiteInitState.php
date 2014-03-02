<?php


namespace Drupal\Core\Site;


/**
 * Represents the state of the process with regard to choosing a site directory.
 *
 * This class is typically instantiated
 * - from Site::init(), during a normal request. In this case
 * Site::initInstaller()
 */
class SiteInitState {

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
   * A service that can choose a site in a multisite scenario.
   *
   * @var \Drupal\Core\Site\SitePicker
   */
  private $sitePicker;

  /**
   * @param bool $is_installation_process
   *   TRUE, if the process is a site installation process.
   *
   * @return self
   */
  public static function createFromEnvironment($is_installation_process) {
    $site_picker = SitePicker::createFromEnvironment();
    return new self($site_picker, $is_installation_process);
  }

  /**
   * Constructs the site state object.
   *
   * @param SitePicker $site_picker
   *   A service that can choose a site in a multisite scenario.
   * @param bool $is_installation_process
   *   TRUE, if the process is a site installation process.
   */
  public function __construct($site_picker, $is_installation_process) {
    $this->isInstallationProcess = $is_installation_process;
    $this->sitePicker = $site_picker;
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
   * @param string $root_directory
   *   The root directory to use for absolute paths; i.e., DRUPAL_ROOT.
   * @param array|null $sites
   *   (optional) A multi-site mapping, as defined in settings.php, or
   *   NULL, if multisite functionality is not enabled.
   * @param string $custom_path
   *   (optional) An explicit site path to set; skipping site negotiation.
   *
   * @throws \BadMethodCallException
   */
  public function initializePath($root_directory, array $sites = NULL, $custom_path = NULL) {
    if (isset($this->siteDirectory)) {
      throw new \BadMethodCallException('Site path is already initialized.');
    }
    // Force-override the site directory in tests.
    // @todo Should this be passed in through the constructor to make this fully unit-testable?
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
      $path = $this->sitePicker->discoverPath($root_directory, $sites, !$this->isInstallationProcess);
    }
    // If the multi-site functionality is not enabled, the Drupal root
    // directory is the site directory.
    else {
      $path = '';
    }
    $this->siteDirectory = new SiteDirectory($root_directory, $path);
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
