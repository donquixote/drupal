<?php
namespace Drupal\Tests\Core\Extension;

use Drupal\Core\Extension\DirectoryToFiles\DirectoryToFiles_StaticGrep;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionsByName\Builder\ExtensionsByNameBuilder;
use Drupal\Core\Extension\FilesToInfo\FilesToInfo_Static;
use Drupal\Core\Extension\FilesToTypes\FilesToTypes_Static;
use Drupal\Core\Extension\ProfileName\ProfileName_Static;
use Drupal\Core\Extension\RawExtensionsByType\RawExtensionsByType_FromRawExtensionsGrouped;
use Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common;
use Drupal\Core\Extension\SearchdirToFilesGrouped\SearchdirToFilesGrouped_Common;
use Drupal\Core\Extension\SearchdirToRawExtensionsGrouped\SearchdirToRawExtensionsGroupedSingleton;
use Drupal\Core\StringTranslation\PluralTranslatableMarkup;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Tests\UnitTestCase;

class ExtensionsByNameTest extends UnitTestCase {

  /**
   * Tests the extension list discovery with fake directory scans.
   */
  public function testExtensionsByNameVirtual() {

    $info_by_file = [
      'core/profiles/standard/standard.info.yml' => [
        'type' => 'profile',
      ],
      'core/profiles/minimal/minimal.info.yml' => [
        'type' => 'profile',
      ],
      // Override the core instance of the 'minimal' profile.
      'sites/default/profiles/minimal/minimal.info.yml' => [
        'type' => 'profile',
      ],
      'profiles/myprofile/myprofile.info.yml' => [
        'type' => 'profile',
        'dependencies' => [
          'myprofile_nested_module',
        ],
      ],
      'profiles/myprofile/modules/myprofile_nested_module/myprofile_nested_module.info.yml' => [],
      'profiles/otherprofile/otherprofile.info.yml' => [
        'type' => 'profile',
      ],
      'core/modules/user/user.info.yml' => [],
      'profiles/otherprofile/modules/otherprofile_nested_module/otherprofile_nested_module.info.yml' => [],
      'core/modules/system/system.info.yml' => [],
      'core/themes/seven/seven.info.yml' => [
        'type' => 'theme',
      ],
      // Override the core instance of the 'seven' theme.
      'sites/default/themes/seven/seven.info.yml' => [
        'type' => 'theme',
      ],
      'modules/devel/devel.info.yml' => [],
      'modules/poorly_placed_theme/poorly_placed_theme.info.yml' => [
        'type' => 'theme',
      ],
    ];

    $files = [];
    $types_by_file = [];
    $files_by_type_and_name_expected = [];
    foreach ($info_by_file as $file => &$info) {
      $files[] = $file;
      $name = basename($file, '.info.yml');
      $info += [
        'type' => 'module',
        'name' => "Name of ($name)",
      ];
      $type = $info['type'];
      $types_by_file[$file] = $type;
      $files_by_type_and_name_expected[$type][$name] = $file;
    }
    unset($info);

    unset($files_by_type_and_name_expected['module']['otherprofile_nested_module']);
    $files_by_type_and_name_expected['module']['myprofile'] = 'profiles/myprofile/myprofile.info.yml';

    $directoryToFiles = new DirectoryToFiles_StaticGrep($files);
    $filesToTypes = new FilesToTypes_Static($types_by_file, 'module');
    $searchdirToFilesGrouped = SearchdirToFilesGrouped_Common::createFromComponents($directoryToFiles, $filesToTypes);

    $root = '/DRUPAL_ROOT';
    $searchdirToRawExtensionsGrouped = SearchdirToRawExtensionsGroupedSingleton::createInstance($root, $searchdirToFilesGrouped);

    $rawExtensionsByType = RawExtensionsByType_FromRawExtensionsGrouped::create(
      new SearchdirPrefixes_Common('sites/default', FALSE),
      $searchdirToRawExtensionsGrouped,
      new ProfileName_Static('myprofile'));

    $installed_weights = [
      'module' => [
        'myprofile' => 999,
        'system' => 0,
        'user' => 2,
      ],
      'theme' => [
        'seven' => 2,
      ],
    ];

    $lists = ExtensionsByNameBuilder::create($rawExtensionsByType)
      ->withFilesToInfo(new FilesToInfo_Static($info_by_file))
      ->withActiveProfileName('myprofile')
      ->withInstalledWeightsStatic($installed_weights)
      ->withoutMtime()
      // Avoid the module handler.
      # ->withSystemInfoAlter()
      ->withTranslationService(new FakeTranslationManager())
      ->buildAll();

    $extensions_by_type = [];
    $files_by_type_and_name = [];
    foreach ($lists as $type => $list) {
      $extensions_by_type[$type] = $type_extensions_by_name = $list->getExtensions();
      foreach ($type_extensions_by_name as $name => $extension) {
        $files_by_type_and_name[$type][$name] = $extension->getPathname();
      }
    }

    $this->assertArrayEquals($files_by_type_and_name_expected, $files_by_type_and_name);

    $extension_expected = new Extension($root, 'module', 'core/modules/system/system.info.yml', 'system.module');
    $extension_expected->info = [
      'type' => 'module',
      'name' => 'Name of (system)',
      'dependencies' => [],
      'description' => '',
      'package' => 'Other',
      'version' => NULL,
      'php' => DRUPAL_MINIMUM_PHP,
    ];
    $extension_expected->subpath = 'modules/system/system.info.yml';
    $extension_expected->origin = 'core';
    $extension_expected->required_by = [];
    $extension_expected->requires = [];
    $extension_expected->sort = 0;
    $extension_expected->weight = 0;
    $extension_expected->status = 1;
    $extension_expected->schema_version = -1;

    static::assertEquals($extension_expected, $extensions_by_type['module']['system'], 'system');

    $extension_expected = new Extension($root, 'module', 'profiles/myprofile/modules/myprofile_nested_module/myprofile_nested_module.info.yml', 'myprofile_nested_module.module');
    $extension_expected->info = [
      'type' => 'module',
      # 'required' => TRUE,
      'name' => 'Name of (myprofile_nested_module)',
      'dependencies' => [],
      'description' => '',
      'package' => 'Other',
      'version' => NULL,
      'php' => DRUPAL_MINIMUM_PHP,
      'required' => TRUE,
      'explanation' => new TranslatableMarkup('Dependency of required module @module', ['@module' => 'Name of (myprofile)'], [], new FakeTranslationManager()),
    ];
    $extension_expected->subpath = 'profiles/myprofile/modules/myprofile_nested_module/myprofile_nested_module.info.yml';
    $extension_expected->origin = '';
    $extension_expected->required_by = [
      'myprofile' => [
        'name' => 'myprofile_nested_module',
      ],
    ];
    $extension_expected->requires = [];
    $extension_expected->sort = 0;
    $extension_expected->weight = 0;
    $extension_expected->status = 0;
    $extension_expected->schema_version = -1;

    static::assertEquals($extension_expected, $extensions_by_type['module']['myprofile_nested_module'], 'myprofile_nested_module');

    $extension_expected = new Extension($root, 'profile', 'profiles/myprofile/myprofile.info.yml', 'myprofile.profile');
    $extension_expected->info = [
      'type' => 'profile',
      'name' => 'Name of (myprofile)',
      'dependencies' => [
        'myprofile_nested_module',
      ],
      'description' => '',
      'package' => 'Other',
      'version' => NULL,
      'php' => DRUPAL_MINIMUM_PHP,
      'hidden' => TRUE,
      'required' => TRUE,
      'distribution' => [
        'name' => 'Drupal',
      ],
    ];
    $extension_expected->subpath = 'profiles/myprofile/myprofile.info.yml';
    $extension_expected->origin = '';
    $extension_expected->required_by = [];
    $extension_expected->requires = [
      'myprofile_nested_module' => [
        'name' => 'myprofile_nested_module',
      ],
    ];
    $extension_expected->sort = -1;
    // @todo Check if this is the intended behavior.
    $extension_expected->weight = 999;
    $extension_expected->status = 1;
    $extension_expected->schema_version = -1;

    static::assertEquals($extension_expected, $extensions_by_type['module']['myprofile'], 'myprofile');
  }

}

/**
 * Implements a translation manager in tests.
 */
class FakeTranslationManager implements TranslationInterface {

  /**
   * {@inheritdoc}
   */
  public function translate($string, array $args = array(), array $options = array()) {
    return new TranslatableMarkup($string, $args, $options, $this);
  }

  /**
   * {@inheritdoc}
   */
  public function translateString(TranslatableMarkup $translated_string) {
    return $translated_string->getUntranslatedString();
  }

  /**
   * {@inheritdoc}
   */
  public function formatPlural($count, $singular, $plural, array $args = array(), array $options = array()) {
    return new PluralTranslatableMarkup($count, $singular, $plural, $args, $options, $this);
  }

}
