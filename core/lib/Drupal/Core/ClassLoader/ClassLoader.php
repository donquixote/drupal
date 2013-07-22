<?php

namespace Drupal\Core\ClassLoader;


class ClassLoader extends AbstractClassLoader {

  private $prefixes = array();
  private $fallbackDirs = array();

  /**
   * Registers a set of classes, merging with any others previously set.
   *
   * @param string       $prefix  The classes prefix
   * @param array|string $paths   The location(s) of the classes
   * @param bool         $prepend Prepend the location(s)
   */
  public function add($prefix, $paths, $prepend = false) {
    if (!$prefix) {
      if ($prepend) {
        $this->fallbackDirs = array_merge(
          (array) $paths,
          $this->fallbackDirs
        );
      } else {
        $this->fallbackDirs = array_merge(
          $this->fallbackDirs,
          (array) $paths
        );
      }

      return;
    }

    $first = $prefix[0];
    if (!isset($this->prefixes[$first][$prefix])) {
      $this->prefixes[$first][$prefix] = (array) $paths;

      return;
    }
    if ($prepend) {
      $this->prefixes[$first][$prefix] = array_merge(
        (array) $paths,
        $this->prefixes[$first][$prefix]
      );
    } else {
      $this->prefixes[$first][$prefix] = array_merge(
        $this->prefixes[$first][$prefix],
        (array) $paths
      );
    }
  }

  /**
   * @param $extensionName
   * @param $relativeExtensionDir
   * @param bool $prepend
   */
  public function addDrupalExtension($extensionName, $relativeExtensionDir, $prepend = false) {
    $this->add('Drupal\\' . $extensionName . '\\',
      DRUPAL_ROOT . DIRECTORY_SEPARATOR . $relativeExtensionDir . '/lib',
      $prepend);
  }

  /**
   * @param $extensionName
   * @param $relativeExtensionDir
   * @param bool $prepend
   */
  public function addDrupalExtensionTests($extensionName, $relativeExtensionDir, $prepend = false) {
    $this->add('Drupal\\' . $extensionName . '\Tests\\',
      DRUPAL_ROOT . DIRECTORY_SEPARATOR . $relativeExtensionDir . '/tests',
      $prepend);
  }

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param string $class The name of the class
   *
   * @return string|false The path if found, false otherwise
   */
  public function findFile($class) {
    // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (isset($this->classMap[$class])) {
      return $this->classMap[$class];
    }

    if (false !== $pos = strrpos($class, '\\')) {
      // namespaced class name
      $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      $className = substr($class, $pos + 1);
    } else {
      // PEAR-like class name
      $classPath = null;
      $className = $class;
    }

    $classPath .= strtr($className, '_', DIRECTORY_SEPARATOR) . '.php';

    $first = $class[0];
    if (isset($this->prefixes[$first])) {
      foreach ($this->prefixes[$first] as $prefix => $dirs) {
        if (0 === strpos($class, $prefix)) {
          foreach ($dirs as $dir) {
            if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
              return $dir . DIRECTORY_SEPARATOR . $classPath;
            }
          }
        }
      }
    }

    foreach ($this->fallbackDirs as $dir) {
      if (file_exists($dir . DIRECTORY_SEPARATOR . $classPath)) {
        return $dir . DIRECTORY_SEPARATOR . $classPath;
      }
    }

    if ($this->useIncludePath && $file = stream_resolve_include_path($classPath)) {
      return $file;
    }

    return $this->classMap[$class] = false;
  }
}