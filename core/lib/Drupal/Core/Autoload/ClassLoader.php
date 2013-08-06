<?php

/**
 * @file
 * Contains Drupal\Core\Autoload\ClassLoader.
 */

namespace Drupal\Core\Autoload;

/**
 * Implements a PSR-0 class loader.
 * See https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 *
 * The class is mostly copied from Composer\Autoload\ClassLoader, adapted to the
 * Drupal coding standards, and enhanced with docblock comments.
 *
 * Original authors of Symfony UniversalClassLoader and
 * Composer\Autoload\ClassLoader:
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ClassLoader {

  /**
   * @var array
   *   Prefixes mapped to PSR-0 directories.
   *
   *   The array has a nested structure, where prefixes are grouped by their
   *   first character.
   *
   *   E.g. a possible value of this variable could be:
   *
   *   array(
   *     'D' => array(
   *       'Drupal\Core\\' => array(DRUPAL_ROOT . '/core/lib'),
   *       'Drupal\Component\\' => array(DRUPAL_ROOT . '/core/lib'),
   *       'Drupal\system\\' => array(DRUPAL_ROOT . '/core/modules/system/lib'),
   *     ),
   *     'S' => array(
   *       'Symfony\Component\Routing\\' => array(..),
   *       'Symfony\Component\Process\\' => array(..),
   *     ),
   *   ),
   */
  private $prefixes = array();

  /**
   * @var array
   *   PSR-0 directories to use if no matching prefix is found.
   */
  private $fallbackDirs = array();

  /**
   * @var bool
   *   TRUE, if the autoloader uses the include path to check for classes.
   */
  private $useIncludePath = FALSE;

  /**
   * @var array
   *   Specific classes mapped to specific PHP files.
   */
  private $classMap = array();

  /**
   * Gets the registered prefixes for PSR-0 directories.
   *
   * @return array
   *   Registered prefixes mapped to PSR-0 directories.
   */
  public function getPrefixes() {
    return call_user_func_array('array_merge', $this->prefixes);
  }

  /**
   * Gets the fallback directories.
   *
   * @return array
   *   PSR-0 directories to use if no matching prefix is found.
   */
  public function getFallbackDirs() {
    return $this->fallbackDirs;
  }

  /**
   * Gets the class map.
   *
   * @return array
   *   Specific classes mapped to specific PHP files.
   */
  public function getClassMap() {
    return $this->classMap;
  }

  /**
   * Adds a class map.
   *
   * @param array $classMap
   *   Specific classes mapped to specific PHP files.
   */
  public function addClassMap(array $classMap) {
    if ($this->classMap) {
      $this->classMap = array_merge($this->classMap, $classMap);
    }
    else {
      $this->classMap = $classMap;
    }
  }

  /**
   * Registers a set of classes, merging with any others previously set.
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $paths
   *   The location(s) of the classes
   * @param bool $prepend
   *   Whether to prepend the location(s)
   */
  public function add($prefix, $paths, $prepend = FALSE) {
    if (!$prefix) {
      if ($prepend) {
        $this->fallbackDirs = array_merge(
          (array) $paths,
          $this->fallbackDirs
        );
      }
      else {
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
    }
    else {
      $this->prefixes[$first][$prefix] = array_merge(
        $this->prefixes[$first][$prefix],
        (array) $paths
      );
    }
  }

  /**
   * Registers a set of classes, replacing any others previously set.
   *
   * @param string $prefix
   *   The classes prefix
   * @param array|string $paths
   *   The location(s) of the classes
   */
  public function set($prefix, $paths) {
    if (!$prefix) {
      $this->fallbackDirs = (array) $paths;

      return;
    }
    $this->prefixes[substr($prefix, 0, 1)][$prefix] = (array) $paths;
  }

  /**
   * Turns on searching the include path for class files.
   *
   * @param bool $useIncludePath
   */
  public function setUseIncludePath($useIncludePath) {
    $this->useIncludePath = $useIncludePath;
  }

  /**
   * Can be used to check if the autoloader uses the include path to check
   * for classes.
   *
   * @return bool
   */
  public function getUseIncludePath() {
    return $this->useIncludePath;
  }

  /**
   * Registers this instance as an autoloader.
   *
   * @param bool $prepend
   *   Whether to prepend the autoloader or not
   */
  public function register($prepend = false) {
    spl_autoload_register(array($this, 'loadClass'), true, $prepend);
  }

  /**
   * Unregisters this instance as an autoloader.
   */
  public function unregister() {
    spl_autoload_unregister(array($this, 'loadClass'));
  }

  /**
   * Loads the given class or interface.
   *
   * @param string $class
   *   The name of the class
   * @return bool|NULL
   *   TRUE if loaded, NULL otherwise
   */
  public function loadClass($class) {
    if ($file = $this->findFile($class)) {
      include $file;

      return TRUE;
    }
  }

  /**
   * Finds the path to the file where the class is defined.
   *
   * @param string $class
   *   The name of the class
   *
   * @return string|FALSE
   *   The path if found, FALSE otherwise
   */
  public function findFile($class) {
    // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
    if ('\\' == $class[0]) {
      $class = substr($class, 1);
    }

    if (isset($this->classMap[$class])) {
      return $this->classMap[$class];
    }

    if (FALSE !== $pos = strrpos($class, '\\')) {
      // namespaced class name.
      $classPath = strtr(substr($class, 0, $pos), '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
      $className = substr($class, $pos + 1);
    }
    else {
      // PEAR-like class name.
      $classPath = NULL;
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

    return $this->classMap[$class] = FALSE;
  }

}
