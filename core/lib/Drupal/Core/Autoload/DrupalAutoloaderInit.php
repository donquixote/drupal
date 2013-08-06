<?php

/**
 * @file
 * Contains Drupal\Core\Autoload\DrupalAutoloaderInit.
 */

namespace Drupal\Core\Autoload;

/**
 * Provides and holds a singleton class loader instance.
 *
 * This class is mostly based on the AutoloaderInit generated by Composer, with
 * the modification that it uses Drupal's own ClassLoader class, instead of the
 * Composer's ClassLoader class.
 */
class DrupalAutoloaderInit {

  /**
   * @var \Drupal\Core\Autoload\ClassLoader
   *   Singleton class loader instance, that is initialized in getLoader().
   */
  private static $loader;

  /**
   * Autoload callback to load the Drupal\Core\Autoload\ClassLoader class.
   * This would not be strictly needed in Drupal, but it stays here to remain as
   * close as possible to the AutoloaderInit generated by Composer.
   *
   * @param string $class
   *   The name of the class to load.
   *   If this is anything other than "Drupal\Core\Autoload\ClassLoader", this
   *   method will do nothing.
   */
  public static function loadClassLoader($class) {
    if ('Drupal\Core\Autoload\ClassLoader' === $class) {
      require __DIR__ . '/ClassLoader.php';
    }
  }

  /**
   * Get the lazy-created class loader instance.
   *
   * @return \Drupal\Core\Autoload\ClassLoader
   *   Singleton instance of the class loader.
   */
  public static function getLoader() {
    // Check if the class loader is already initialized.
    if (NULL !== self::$loader) {
      return self::$loader;
    }

    // Temporarily register an autoload callback to load the
    // Drupal\Core\Autoload\ClassLoader class.
    // This could be done with a simple require_once, but instead it is done
    // in the same way as Composer does it.
    spl_autoload_register(array('Drupal\Core\Autoload\DrupalAutoloaderInit', 'loadClassLoader'), true, true);
    self::$loader = $loader = new ClassLoader();
    spl_autoload_unregister(array('Drupal\Core\Autoload\DrupalAutoloaderInit', 'loadClassLoader'));

    // The DRUPAL_ROOT . '/core' directory.
    $coreDir = dirname(dirname(dirname(dirname(__DIR__))));

    // Directory that contains 3rd party code downloaded by Composer.
    $vendorDir = $coreDir . '/vendor';

    // Directory that contains autoload data generated by Composer.
    $composerDir = $vendorDir . '/composer';

    // Set the PHP include path.
    $includePaths = require $composerDir . '/include_paths.php';
    array_push($includePaths, get_include_path());
    set_include_path(join(PATH_SEPARATOR, $includePaths));

    // Load PSR-0 namespaces to be registered in the class loader.
    $map = require $composerDir . '/autoload_namespaces.php';

    // Register core namespaces as PSR-4 instead of PSR-0,
    // to let the autoloader handle them with priority.
    // These mappings need to be equivalent with the PSR-0 mappings specified in
    // composer.json. This is only possible because class names in Drupal core
    // do not contain underscores.
    // @todo Do this via composer.json, once Composer supports PSR-4.
    unset($map['Drupal\Core']);
    unset($map['Drupal\Component']);
    unset($map['Drupal\Driver']);
    $loader->setPsr4('Drupal\Core\\', $baseDir . '/core/lib/Drupal/Core');
    $loader->setPsr4('Drupal\Component\\', $baseDir . '/core/lib/Drupal/Component');
    $loader->setPsr4('Drupal\Driver\\', $baseDir . '/drivers/lib/Drupal/Driver');

    // Register the remaining PSR-0 namespaces - mostly for vendor libraries.
    foreach ($map as $namespace => $path) {
      $loader->set($namespace, $path);
    }

    // Register the class map in the class loader.
    $classMap = require $composerDir . '/autoload_classmap.php';
    if ($classMap) {
      $loader->addClassMap($classMap);
    }

    // Register the class loader on the SPL autoload stack.
    $loader->register(true);

    // Include specific php files, for packages that use the
    // 'autoload' > 'files' directive in composer.json.
    $includeFiles = require $composerDir . '/autoload_files.php';
    foreach ($includeFiles as $file) {
      require $file;
    }

    return $loader;
  }

}
