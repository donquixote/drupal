<?php

namespace Drupal\Core\Extension\ExtensionsByName;

use Drupal\Component\Graph\Graph;
use Drupal\Core\Extension\ModuleHandler;

final class ExtensionsByNameUtil {

  private function __construct() {}

  /**
   * Adds dependency information to the extension objects.
   *
   * Keys added:
   * - $extension->requires
   *   An array with the keys being the modules that this module requires.
   * - $extension->required_by
   *   An array with the keys being the modules that will not work without this module.
   * - $extension->sort
   *   A weight based on the dependency tree.
   *
   * @param \Drupal\Core\Extension\Extension[] $extensions
   *   Extension objects that will be modified, with $extension->info array
   *   parsed from the *.info.yml file.
   *   Format: $[$extension_name] = $extension
   *
   * @see \Drupal\Core\Extension\List_\Processor\ExtensionListProcessor_Dependencies
   */
  public static function buildDependencies(array $extensions) {

    $graph = [];
    foreach ($extensions as $extension) {
      $graph[$extension->getName()]['edges'] = array();
      if (isset($extension->info['dependencies']) && is_array($extension->info['dependencies'])) {
        foreach ($extension->info['dependencies'] as $dependency) {
          $dependency_data = ModuleHandler::parseDependency($dependency);
          $graph[$extension->getName()]['edges'][$dependency_data['name']] = $dependency_data;
        }
      }
    }

    $graph_object = new Graph($graph);

    $graph = $graph_object->searchAndSort();

    foreach ($graph as $name => $data) {
      $extensions[$name]->required_by = isset($data['reverse_paths']) ? $data['reverse_paths'] : array();
      $extensions[$name]->requires = isset($data['paths']) ? $data['paths'] : array();
      $extensions[$name]->sort = $data['weight'];
    }
  }

  /**
   * @param string $type
   *
   * @return array
   *   Defaults to be merged into the info array.
   */
  public static function typeGetDefaults($type) {

    switch ($type) {

      case 'profile':
        return [
          'dependencies' => [],
          'description' => '',
          'package' => 'Other',
          'version' => NULL,
          'php' => DRUPAL_MINIMUM_PHP,
        ];

      case 'module':
        return [
          'dependencies' => [],
          'description' => '',
          'package' => 'Other',
          'version' => NULL,
          'php' => DRUPAL_MINIMUM_PHP,
        ];

      default:
        return [];
    }
  }

}
