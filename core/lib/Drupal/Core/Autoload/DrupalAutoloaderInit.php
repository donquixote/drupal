<?php

/**
 * @file
 * Contains Drupal\Core\Autoload\DrupalAutoloaderInit.
 */

namespace Drupal\Core\Autoload;

class DrupalAutoloaderInit
{
  private static $loader;

  public static function loadClassLoader($class) {
    if ('Drupal\Core\Autoload\ClassLoader' === $class) {
      require __DIR__ . '/ClassLoader.php';
    }
  }

  public static function getLoader() {
    if (null !== self::$loader) {
      return self::$loader;
    }

    spl_autoload_register(array('Drupal\Core\Autoload\DrupalAutoloaderInit', 'loadClassLoader'), true, true);
    self::$loader = $loader = new ClassLoader();
    spl_autoload_unregister(array('Drupal\Core\Autoload\DrupalAutoloaderInit', 'loadClassLoader'));

    $coreDir = dirname(dirname(dirname(dirname(__DIR__))));
    $vendorDir = $coreDir . '/vendor';
    $composerDir = $vendorDir . '/composer';
    $baseDir = dirname($coreDir);

    $includePaths = require $composerDir . '/include_paths.php';
    array_push($includePaths, get_include_path());
    set_include_path(join(PATH_SEPARATOR, $includePaths));

    $map = require $composerDir . '/autoload_namespaces.php';
    unset($map['Drupal\Core']);
    unset($map['Drupal\Component']);
    unset($map['Drupal\Driver']);
    $loader->setPsr4('Drupal\Core\\', $baseDir . '/core/lib/Drupal/Core');
    $loader->setPsr4('Drupal\Component\\', $baseDir . '/core/lib/Drupal/Component');
    $loader->setPsr4('Drupal\Driver\\', $baseDir . '/core/lib/Drupal/Driver');
    foreach ($map as $namespace => $path) {
      $loader->set($namespace, $path);
    }

    $classMap = require $composerDir . '/autoload_classmap.php';
    if ($classMap) {
      $loader->addClassMap($classMap);
    }

    $loader->register(true);

    // @todo This can be updated once Composer provides a autoload_files.php.
    require $vendorDir . '/kriswallsmith/assetic/src/functions.php';
    require $baseDir . '/core/lib/Drupal.php';

    return $loader;
  }
}
