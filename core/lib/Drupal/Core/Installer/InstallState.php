<?php


namespace Drupal\Core\Installer;

use Drupal\Component\MiniContainer\MiniContainerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Installer\Exception\AlreadyInstalledException;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Current state of a Drupal installation.
 *
 * @property string[] ConfigDirectories
 * @property bool ConfigVerified
 *   TRUE, if 'active' and 'staging' config directories are configured in
 *   settings.php, the directories exist and they are writable.
 * @property bool DatabaseVerified
 * @property bool SettingsVerified
 * @property bool BaseSystemVerified
 *
 * @property string VerifyCompletedTask
 *
 * @property \Drupal\Core\State\StateInterface StateService
 *
 * @property bool Finished
 *   TRUE, if the installation is finished.
 *
 * @property TranslationInterface StringTranslationService
 *
 * @property \Drupal\Core\Extension\Extension[] Profiles
 *
 * @property array Parameters
 * @property string ProfileName
 * @property \Drupal\Core\Extension\Extension|null Profile
 * @property string LangCode
 * @property array ProfileInfo
 * @property string ThemeName
 * @property string[] Translations
 *
 * @property string ServerPattern
 *
 * @property bool RequireProfileLoaded
 *
 * @property array[] InstallTasks
 */
class InstallState extends MiniContainerBase {

  /**
   * @var \Drupal\Core\CoreContainer\CoreServices
   */
  private $coreServices;

  /**
   * @return bool
   */
  public function isInteractive() {
    return TRUE;
  }

  /**
   * @return bool
   *   TRUE, if the installation is finished.
   */
  protected function get_Finished() {
    # return $this->state['installation_finished'];
  }

  /**
   * @return array[]
   *
   * @see InstallState::InstallTasks
   */
  protected function get_InstallTasks() {

  }

  /**
   * @return string[]
   *   Format: array('active' => $dir, 'staging' => $dir)
   *   Can be an empty array, if settings.php does not exist or does not set
   *   these directories.
   * @throws \Exception
   *
   * @see InstallState::ConfigDirectories
   */
  protected function get_ConfigDirectories() {
    // @todo This should go into the main CoreContainer.
    // Require that Settings::initialize() was called.
    // This will include settings.php if it exists, and it will initialize the
    // global variable $config_directories.
    $this->coreServices->BootState->SiteSettingsInitialized;
    if (!is_array($GLOBALS['config_directories'])) {
      throw new \Exception('$GLOBALS["config_directories"] is undefined or not an array.');
    }
    return $GLOBALS['config_directories'];
  }

  /**
   * @return bool
   *   TRUE, if 'active' and 'staging' config directories are configured in
   *   settings.php, the directories exist and they are writable.
   *
   * @see InstallState::ConfigVerified
   */
  protected function get_ConfigVerified() {
    $config_directories = $this->ConfigDirectories;
    foreach ([CONFIG_ACTIVE_DIRECTORY, CONFIG_STAGING_DIRECTORY] as $type) {
      if ( empty($config_directories[$type])
        || !is_dir($config_directories[$type])
        || !is_writable($config_directories[$type])
      ) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * @return bool
   *   TRUE, if the database passes verification.
   *
   * @see InstallState::DatabaseVerified
   * @see install_verify_database_settings()
   */
  protected function get_DatabaseVerified() {
    // Require that the database was initialized.
    $this->coreServices->BootState->SiteSettingsInitialized;
    // @todo Get rid of these static calls to impure functions.
    $databases = Database::getConnectionInfo();
    if (empty($databases) || empty($databases['default'])) {
      return FALSE;
    }
    $database = $databases['default'];
    $settings_file = './' . $this->coreServices->Parameters->SitePath . '/settings.php';
    // @todo Get rid of these static calls to impure functions.
    $errors = install_database_errors($database, $settings_file);
    return empty($errors);
  }

  /**
   * @return bool
   *
   * @see InstallState::SettingsVerified
   */
  protected function get_SettingsVerified() {
    return $this->ConfigVerified && $this->DatabaseVerified;
  }

  /**
   * @return bool
   */
  protected function get_BaseSystemVerified() {
    if (!$this->SettingsVerified) {
      return FALSE;
    }
    try {
      $system_schema = system_schema();
      end($system_schema);
      $table = key($system_schema);
      // We already know at this point that the database is initialized, thanks
      // to SettingsVerified.
      // @todo Get rid of these static calls to impure functions.
      return Database::getConnection()->schema()->tableExists($table);
    }
    catch (DatabaseExceptionWrapper $e) {
      // The last defined table of the base system_schema() does not exist yet.
      // $install_state['base_system_verified'] defaults to FALSE, so the code
      // following below will use the minimal installer service container.
      // As soon as the base system is verified here, the installer operates in
      // a full and regular Drupal environment, without any kind of exceptions.
      return FALSE;
    }
  }

  /**
   * @return \Drupal\Core\State\StateInterface
   *
   * @see InstallState::StateService
   * @see install_verify_completed_task()
   */
  protected function get_StateService() {
    return $this->coreServices->Container->get('state');
  }

  /**
   * @return TranslationInterface
   *
   * @see InstallState::StringTranslationService
   */
  protected function get_StringTranslationService() {
    return $this->coreServices->Container->get('string_translation');
  }

  /**
   * @return string|null
   *   The task name or NULL.
   *
   * @see install_verify_completed_task()
   */
  protected function get_VerifyCompletedTask() {
    if (!$this->DatabaseVerified) {
      return NULL;
    }
    $task = $this->StateService->get('install_task');
    if (!isset($task)) {
      return NULL;
    }
    if ($task == 'done') {
      throw new AlreadyInstalledException($this->StringTranslationService);
    }
    return $task;
  }

  /**
   * @return array
   *
   * @see install_profile_info()
   * @see InstallState::ProfileInfo
   */
  protected function get_ProfileInfo() {
    $this->RequireProfileLoaded;
    // @todo Get rid of these static calls to impure functions.
    return install_profile_info($this->ProfileName, $this->LangCode);
  }

  /**
   * @return bool
   * @throws \Exception
   *
   * @see InstallState::RequireProfileLoaded
   */
  protected function get_RequireProfileLoaded() {
    $profile = $this->Profile;
    if (empty($profile)) {
      throw new \Exception("No active profile.");
    }
    $profile->load();
    return TRUE;
  }

  /**
   * @return \Drupal\Core\Extension\Extension|null
   *
   * @see InstallState::Profile
   */
  protected function get_Profile() {
    $profiles = $this->Profiles;
    $profile_name = $this->ProfileName;
    if (!isset($profile_name) || !isset($profiles[$profile_name])) {
      return NULL;
    }
    return $profiles[$profile_name];
  }

  /**
   * @return \Drupal\Core\Extension\Extension[]
   *
   * @see InstallState::Profiles
   */
  protected function get_Profiles() {
    $listing = new ExtensionDiscovery();
    $listing->setProfileDirectories(array());
    return $listing->scan('profile');
  }

  /**
   * @return array
   *
   * @see InstallState::Parameters
   */
  protected function get_Parameters() {
    return $this->coreServices->Request->query->all();
  }

  /**
   * @return string|null
   *
   * @see InstallState::ProfileName
   */
  protected function get_ProfileName() {
    $parameters = $this->Parameters;
    return !empty($parameters['profile'])
      ? preg_replace('/[^a-zA-Z_0-9]/', '', $parameters['profile'])
      : NULL;
  }

  /**
   * @return string|null
   *
   * @see InstallState::LangCode
   */
  protected function get_LangCode() {
    $parameters = $this->Parameters;
    return !empty($parameters['langcode'])
      ? preg_replace('/[^a-zA-Z_0-9\-]/', '', $parameters['langcode'])
      : NULL;
  }

  /**
   * @return string[]
   *
   * @see InstallState::Translations
   * @see install_find_translations()
   */
  protected function get_Translations() {
    $translations = array();
    /** @var \Drupal\Core\StringTranslation\Translator\FileTranslation $translationFinder */
    $translationFinder = $this->coreServices->Container->get('string_translator.file_translation');
    $files = $translationFinder->findTranslationFiles();
    // English does not need a translation file.
    array_unshift($files, (object) array('name' => 'en'));
    foreach ($files as $uri => $file) {
      // Strip off the file name component before the language code.
      $langcode = preg_replace('!^(.+\.)?([^\.]+)$!', '\2', $file->name);
      // Language codes cannot exceed 12 characters to fit into the {language}
      // table.
      if (strlen($langcode) <= 12) {
        $translations[$langcode] = $uri;
      }
    }
    return $translations;
  }

  /**
   * @return bool
   */
  protected function isKeepEnglish() {
    // @todo Allow the install profile to alter this?
    return FALSE;
  }

  /**
   * Gets the theme name, if one is specified in the install profile.
   *
   * @return string
   * @see InstallState::ThemeName
   */
  protected function get_ThemeName() {
    $profile_info = $this->ProfileInfo;
    return isset($profile_info['distribution']['install']['theme'])
      ? $profile_info['distribution']['install']['theme']
      : 'seven';
  }

  /**
   * @return string
   * @see InstallState::ServerPattern
   */
  protected function get_ServerPattern() {
    // @todo Allow the install profile to alter this?
    return 'http://ftp.drupal.org/files/translations/%core/%project/%project-%version.%language.po';
  }

} 
