<?php


namespace Drupal\Core\FrontController;

use Drupal\Core\Config\BootstrapConfigStorageFactory;
use Drupal\Core\CoreContainer\CoreServices;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Installer\Exception\AlreadyInstalledException;
use Drupal\Core\Installer\Exception\InstallerException;
use Drupal\Core\Language\Language;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\Translator\FileTranslation;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;

/**
 * Front controller for site installation.
 */
class InstallPhp extends FrontControllerBase {

  /**
   * @var CoreServices
   */
  private $coreServices;

  /**
   * @var string
   */
  private $sitePath;

  /**
   * Constructs an InstallPhp object.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\CoreContainer\CoreServices $core_services
   */
  function __construct(Request $request, CoreServices $core_services) {
    parent::__construct($request);
    $this->coreServices = $core_services;
    $this->sitePath = $core_services->Parameters->SitePath;
  }

  /**
   * Executes the front controller operation.
   */
  public function sendResponse() {
    global $install_state;
    $install_state = install_state_defaults();
    $install_state['interactive'] = TRUE;

    try {
      // Begin the page request. This adds information about the current state of
      // the Drupal installation to the passed-in array.
      $this->installBeginRequest($install_state);
      // Based on the installation state, run the remaining tasks for this page
      // request, and collect any output.
      $output = install_run_tasks($install_state);
    }
    catch (InstallerException $e) {
      $output = array(
        '#title' => $e->getTitle(),
        '#markup' => $e->getMessage(),
      );
    }

    // After execution, all tasks might be complete, in which case
    // $install_state['installation_finished'] is TRUE. In case the last task
    // has been processed, remove the global $install_state, so other code can
    // reliably check whether it is running during the installer.
    // @see drupal_installation_attempted()
    $state = $install_state;
    if (!empty($install_state['installation_finished'])) {
      unset($GLOBALS['install_state']);
    }

    // All available tasks for this page request are now complete. Interactive
    // installations can send output to the browser or redirect the user to the
    // next page.
    if ($state['parameters_changed']) {
      // Redirect to the correct page if the URL parameters have changed.
      install_goto(install_redirect_url($state));
    }
    elseif (isset($output)) {
      // Display a page only if some output is available. Otherwise it is
      // possible that we are printing a JSON page and theme output should
      // not be shown.
      install_display_output($output, $state);
    }
    elseif ($state['installation_finished']) {
      // Redirect to the newly installed site.
      install_goto('');
    }
  }

  /**
   *
   */
  private function installParameters() {
    $parameters = $this->request->query->all();
  }

  /**
   * Begins an installation request, modifying the installation state as needed.
   *
   * This function performs commands that must run at the beginning of every page
   * request. It throws an exception if the installation should not proceed.
   *
   * @param array $install_state
   *   An array of information about the current installation state. This is
   *   modified with information gleaned from the beginning of the page request.
   */
  private function installBeginRequest(array &$install_state) {

    $request = $this->request;

    // Add any installation parameters passed in via the URL.
    if ($install_state['interactive']) {
      $install_state['parameters'] += $request->query->all();
    }

    // Validate certain core settings that are used throughout the installation.
    if (!empty($install_state['parameters']['profile'])) {
      $install_state['parameters']['profile'] = preg_replace('/[^a-zA-Z_0-9]/', '', $install_state['parameters']['profile']);
    }
    if (!empty($install_state['parameters']['langcode'])) {
      $install_state['parameters']['langcode'] = preg_replace('/[^a-zA-Z_0-9\-]/', '', $install_state['parameters']['langcode']);
    }

    // Allow command line scripts to override server variables used by Drupal.
    $this->coreServices->StaticContext->BootstrapIncIncluded;

    // If the hash salt leaks, it becomes possible to forge a valid testing user
    // agent, install a new copy of Drupal, and take over the original site.
    // The user agent header is used to pass a database prefix in the request when
    // running tests. However, for security reasons, it is imperative that no
    // installation be permitted using such a prefix.
    if ( FALSE !== strpos($request->server->get('HTTP_USER_AGENT'), 'simpletest')
      && !drupal_valid_test_ua()
    ) {
      header($request->server->get('SERVER_PROTOCOL') . ' 403 Forbidden');
      exit;
    }

    $this->coreServices->BootState->SiteSettingsInitialized;

    // Ensure that procedural dependencies are loaded as early as possible,
    // since the error/exception handlers depend on them.
    $this->coreServices->StaticContext->InstallerProceduralDependenciesIncluded;

    // Create a minimal mocked container to support calls to t() in the pre-kernel
    // base system verification code paths below. The strings are not actually
    // used or output for these calls.
    // @todo Separate API level checks from UI-facing error messages.
    $tmp_container = $this->buildTmpContainer();
    \Drupal::setContainer($tmp_container);

    // Determine whether base system services are ready to operate.
    $install_state['config_verified'] = 1
      && install_verify_config_directory(CONFIG_ACTIVE_DIRECTORY)
      && install_verify_config_directory(CONFIG_STAGING_DIRECTORY);

    $install_state['database_verified'] = install_verify_database_settings();

    $install_state['settings_verified'] = 1
      && $install_state['config_verified']
      && $install_state['database_verified'];

    if ($install_state['settings_verified']) {
      try {
        $system_schema = system_schema();
        end($system_schema);
        $table = key($system_schema);
        $install_state['base_system_verified'] = Database::getConnection()->schema()->tableExists($table);
      }
      catch (DatabaseExceptionWrapper $e) {
        // The last defined table of the base system_schema() does not exist yet.
        // $install_state['base_system_verified'] defaults to FALSE, so the code
        // following below will use the minimal installer service container.
        // As soon as the base system is verified here, the installer operates in
        // a full and regular Drupal environment, without any kind of exceptions.
      }
    }

    // Replace services with in-memory and null implementations. This kernel is
    // replaced with a regular one in drupal_install_system().
    if (!$install_state['base_system_verified']) {
      # $core_services->Parameters->Environment = 'install';
      $GLOBALS['conf']['container_service_providers']['InstallerServiceProvider'] = 'Drupal\Core\Installer\InstallerServiceProvider';
    }

    # $core_services = CoreServices::create()->disableContainerDumping();
    # $core_services->setCustomSitePath($site_path);
    $container = $this->coreServices->Container;
    $container->get('request_stack')->push($request);

    // Register the file translation service.
    if (isset($GLOBALS['config']['locale.settings']['translation.path'])) {
      $directory = $GLOBALS['config']['locale.settings']['translation.path'];
    }
    else {
      $directory = $this->sitePath . '/files/translations';
    }
    $container->set('string_translator.file_translation', new FileTranslation($directory));
    $container->get('string_translation')
      ->addTranslator($container->get('string_translator.file_translation'));

    // Set the default language to the selected language, if any.
    if (isset($install_state['parameters']['langcode'])) {
      $default_language = new Language(array('id' => $install_state['parameters']['langcode']));
      $container->get('language.default')->set($default_language);
      \Drupal::translation()->setDefaultLangcode($install_state['parameters']['langcode']);
    }

    // Add list of all available profiles to the installation state.
    $listing = new ExtensionDiscovery();
    $listing->setProfileDirectories(array());
    $install_state['profiles'] += $listing->scan('profile');

    // Prime drupal_get_filename()'s static cache.
    foreach ($install_state['profiles'] as $name => $profile) {
      drupal_get_filename('profile', $name, $profile->getPathname());
    }

    if ($profile = _install_select_profile($install_state)) {
      $install_state['parameters']['profile'] = $profile;
      install_load_profile($install_state);
      if (isset($install_state['profile_info']['distribution']['install']['theme'])) {
        $install_state['theme'] = $install_state['profile_info']['distribution']['install']['theme'];
      }
    }

    // Override the module list with a minimal set of modules.
    $module_handler = \Drupal::moduleHandler();
    if (!$module_handler->moduleExists('system')) {
      $module_handler->addModule('system', 'core/modules/system');
    }
    if ($profile && !$module_handler->moduleExists($profile)) {
      $module_handler->addProfile($profile, $install_state['profiles'][$profile]->getPath());
    }
    // After setting up a custom and finite module list in a custom low-level
    // bootstrap like here, ensure to use ModuleHandler::loadAll() so that
    // ModuleHandler::isLoaded() returns TRUE, since that is a condition being
    // checked by other subsystems (e.g., the theme system).
    $module_handler->loadAll();

    $this->coreServices->BootState->LegacyRequestPrepared;

    // Prepare for themed output. We need to run this at the beginning of the
    // page request to avoid a different theme accidentally getting set. (We also
    // need to run it even in the case of command-line installations, to prevent
    // any code in the installer that happens to initialize the theme system from
    // accessing the database before it is set up yet.)
    drupal_maintenance_theme();

    if ($install_state['database_verified']) {
      // Verify the last completed task in the database, if there is one.
      $task = install_verify_completed_task();
    }
    else {
      $task = NULL;

      // Do not install over a configured settings.php.
      if (Database::getConnectionInfo()) {
        throw new AlreadyInstalledException($container->get('string_translation'));
      }
    }

    // Ensure that the active configuration is empty before installation starts.
    if ($install_state['config_verified'] && empty($task)) {
      $config = BootstrapConfigStorageFactory::get()->listAll();
      if (!empty($config)) {
        $task = NULL;
        throw new AlreadyInstalledException($container->get('string_translation'));
      }
    }

    // Modify the installation state as appropriate.
    $install_state['completed_task'] = $task;
  }

  /**
   * Creates a minimal mocked container to support calls to t() in the
   * pre-kernel base system verification code paths below. The strings are not
   * actually used or output for these calls.
   *
   * @return \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  private function buildTmpContainer() {
    $container = new ContainerBuilder();
    $container->setParameter('language.default_values', Language::$defaultValues);
    $container
      ->register('language.default', 'Drupal\Core\Language\LanguageDefault')
      ->addArgument('%language.default_values%');
    $container
      ->register('language_manager', 'Drupal\Core\Language\LanguageManager')
      ->addArgument(new Reference('language.default'));
    $container
      ->register('string_translation', 'Drupal\Core\StringTranslation\TranslationManager')
      ->addArgument(new Reference('language_manager'));
    $container
      ->register('path.matcher', 'Drupal\Core\Path\PathMatcher')
      ->addArgument(new Reference('config.factory'));
    return $container;
  }

}
