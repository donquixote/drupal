<?php

namespace Drupal\Core\Autoload;

use Drupal\Component\Autoload\ClassLoaderInterface;
use Drupal\Component\Autoload\ClassLoader;

class ClassLoaderInit {

  private static $loader;

  public static function loadClassLoader($class) {
    if ('Drupal\Component\Autoload\ClassLoader' === $class) {
      require dirname(__DIR__) . '/Component/ClassLoader.php';
    }
  }

  public static function getLoader($drupal_root) {
    if (null !== self::$loader) {
      return self::$loader;
    }

    spl_autoload_register(array('Drupal\Core\Autoload\ClassLoaderInit', 'loadClassLoader'), true, true);
    self::$loader = $loader = new ClassLoader();
    spl_autoload_unregister(array('Drupal\Core\Autoload\ClassLoaderInit', 'loadClassLoader'));

    $coreDir = dirname(dirname(dirname(dirname(__DIR__))));
    $vendorDir = $coreDir . '/vendor';
    $composerDir = $vendorDir . '/composer';
    $baseDir = dirname($coreDir);

    $includePaths = require $composerDir . '/include_paths.php';
    array_push($includePaths, get_include_path());
    set_include_path(join(PATH_SEPARATOR, $includePaths));

    $map = require $composerDir . '/autoload_namespaces.php';
    foreach ($map as $namespace => $path) {
      $loader->set($namespace, $path);
    }

    $classMap = require $composerDir . '/autoload_classmap.php';
    if ($classMap) {
      $loader->addClassMap($classMap);
    }

    $loader->register(true);

    // @todo remove this with new Composer patch.
    require $vendorDir . '/kriswallsmith/assetic/src/functions.php';
    require $baseDir . '/core/lib/Drupal.php';

    return $loader;
  }
}