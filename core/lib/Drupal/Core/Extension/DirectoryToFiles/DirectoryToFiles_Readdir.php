<?php

namespace Drupal\Core\Extension\DirectoryToFiles;

/**
 * Implementation using opendir() and readdir().
 */
class DirectoryToFiles_Readdir implements DirectoryToFilesInterface {

  /**
   * @var string
   */
  private $root;

  /**
   * @var string[]
   */
  private $blacklist;

  /**
   * Regular expression for file names.
   *
   * @var string
   */
  private $pattern;

  /**
   * @param bool $include_tests
   *
   * @return string[]
   */
  public static function createBlacklist($include_tests = FALSE) {

    $blacklist = [
      // Object-oriented code subdirectories.
      'src',
      'lib',
      'vendor',
      // Front-end.
      'assets',
      'css',
      'files',
      'images',
      'js',
      'misc',
      'templates',
      // Legacy subdirectories.
      'includes',
      // Test subdirectories.
      'fixtures',
      // @todo ./tests/Drupal should be ./tests/src/Drupal
      'Drupal',
    ];
    if (!$include_tests) {
      $blacklist[] = 'tests';
    }

    return $blacklist;
  }

  /**
   * Creates a new instance with the default settings.
   *
   * @param string $root
   * @param bool $include_tests
   * @param bool $buffered
   *
   * @return \Drupal\Core\Extension\DirectoryToFiles\DirectoryToFilesInterface
   */
  public static function create($root, $include_tests = FALSE, $buffered = TRUE) {

    $instance = new self(
      $root,
      self::createBlacklist($include_tests),
      '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*.info.yml$/');

    if ($buffered) {
      $instance = new DirectoryToFiles_Buffer($instance);
    }

    return $instance;
  }

  /**
   * @param string $root
   *   The root path.
   * @param string[] $blacklist
   *   E.g. ['src', 'lib', 'vendor', ...]
   * @param string $pattern
   *   Regular expression to check file names.
   */
  public function __construct($root, array $blacklist, $pattern) {
    $this->root = $root;
    $this->blacklist = $blacklist;
    $this->pattern = $pattern;
  }

  /**
   * Resets all cached data.
   */
  public function reset() {
    // Nothing to do.
  }

  /**
   * {@inheritdoc}
   */
  public function getFiles($directory) {
    $dir_absolute = $this->root . '/' . $directory;
    if (!is_dir($dir_absolute)) {
      return [];
    }
    $files = [];
    $this->dirCollectFilesRecursive($files, $dir_absolute, $directory . '/');
    return $files;
  }

  /**
   * @param string[] $files
   *   Array that will contain the files.
   *   Format: $[] = 'core/modules/system/system.info.yml'
   * @param string $dir_absolute
   *   E.g. '/var/www/myproject/core/modules/system'
   * @param string $prefix
   *   E.g. 'modules/', or 'core/modules/system/'.
   */
  private function dirCollectFilesRecursive(array &$files, $dir_absolute, $prefix) {

    if (FALSE === $handle = @opendir($dir_absolute)) {
      return;
    }

    $filenames = [];
    $subdirs = [];
    while (FALSE !== $filename = readdir($handle)) {
      if ('.' === $filename[0]) {
        continue;
      }
      $path_absolute = $dir_absolute . '/' . $filename;
      if (is_dir($path_absolute)) {
        $subdirs[$path_absolute] = $filename;
      }
      elseif (preg_match($this->pattern, $filename)) {
        $filenames[] = $filename;
      }
    }

    sort($filenames);
    asort($subdirs);

    foreach ($filenames as $filename) {
      $files[] = $prefix . $filename;
    }

    closedir($handle);

    // Skip blacklisted subfolder names.
    $subdirs = array_diff($subdirs, $this->blacklist);

    // 'config' directories are special-cased here, because every extension
    // contains one. However, those default configuration directories cannot
    // contain extensions. The directory name cannot be globally skipped,
    // because core happens to have a directory of an actual module that is
    // named 'config'. By explicitly testing for that case, we can skip all
    // other config directories, and at the same time, still allow the core
    // config module to be overridden/replaced in a profile/site directory
    // (whereas it must be located directly in a modules directory).
    if (isset($subdirs[$dir_absolute . '/config'])) {
      if ($prefix !== 'modules/' && substr($prefix, -9) !== '/modules/') {
        unset($subdirs[$dir_absolute . '/config']);
      }
    }

    foreach ($subdirs as $path_absolute => $subdir_name) {
      $this->dirCollectFilesRecursive($files, $path_absolute, $prefix . $subdir_name . '/');
    }
  }
}
