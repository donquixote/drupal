<?php

/**
 * @file
 * Contains \Drupal\Core\StringTranslation\Translator\CustomStrings.
 */

namespace Drupal\Core\StringTranslation\Translator;

use Drupal\Core\Site\Settings\SettingsInterface;

/**
 * String translator using overrides from variables.
 *
 * This is a high performance way to provide a handful of string replacements.
 * See settings.php for examples.
 */
class CustomStrings extends StaticTranslation {

  /**
   * The settings read only object.
   *
   * @var \Drupal\Core\Site\Settings\SettingsInterface
   */
  protected $settings;

  /**
   * Constructs a CustomStrings object.
   *
   * @param \Drupal\Core\Site\Settings\SettingsInterface $settings
   *   The settings read only object.
   */
  public function __construct(SettingsInterface $settings) {
    parent::__construct();
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  protected function getLanguage($langcode) {
    return $this->settings->get('locale_custom_strings_' . $langcode, array());
  }

}
