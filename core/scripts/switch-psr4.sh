#!/bin/php
<?php

namespace Drupal\Core\SwitchPsr4;

/**
 * @file
 * Moves module-provided class files to their PSR-4 location.
 *
 * E.g.:
 * core/modules/action/lib/{Drupal/action → }/ActionAccessController.php
 * core/modules/action/lib/{Drupal/action → }/ActionAddFormController.php
 */

// Determine DRUPAL_ROOT.
$dir = dirname(__FILE__);
while (!defined('DRUPAL_ROOT')) {
  if (is_dir($dir . '/core')) {
    define('DRUPAL_ROOT', $dir);
  }
  $dir = dirname($dir);
}

// Run the script.
run();

/**
 * Runs the script.
 */
function run() {
  $cmd_arguments = $_SERVER['argv'];
  // The first argument is the script name.
  array_shift($cmd_arguments);
  if (!empty($cmd_arguments)) {
    // If one or more arguments are given, treat those arguments as directories,
    // and process all modules found within these directories.
    foreach ($cmd_arguments as $dir) {
      if (!is_dir($dir)) {
        print "The argument '$dir' is not a directory.";
        continue;
      }
      process_extensions_base_dir($dir);
    }
  }
  else {
    // If no arguments are given, process all modules and profiles in the core
    // directories instead.
    process_extensions_base_dir(DRUPAL_ROOT . '/core/modules');
    process_extensions_base_dir(DRUPAL_ROOT . '/core/profiles');
  }
}

/**
 * Scans all subdirectories of a given directory for Drupal extensions, and runs
 * process_extension() for each one that it finds.
 *
 * @param string $dir
 *   A directory whose subdirectories could contain Drupal extensions.
 */
function process_extensions_base_dir($dir) {
  /**
   * @var \DirectoryIterator $fileinfo
   */
  foreach (new \DirectoryIterator($dir) as $fileinfo) {
    if ($fileinfo->isDot()) {
      // do nothing
    }
    elseif ($fileinfo->isDir()) {
      process_candidate_dir($fileinfo->getPathname());
    }
  }
}

/**
 * Recursively scans a directory for Drupal extensions, and runs
 * process_extension() for each one that it finds.
 *
 * @param string $dir
 *   A directory that could be a Drupal extension directory.
 */
function process_candidate_dir($dir) {
  /**
   * @var \DirectoryIterator $fileinfo
   */
  foreach (new \DirectoryIterator($dir) as $fileinfo) {
    if ($fileinfo->isDot()) {
      // Ignore "." and "..".
    }
    elseif ($fileinfo->isDir()) {
      // It's a directory.
      switch ($fileinfo->getFilename()) {
        case 'lib':
          // Ignore these directory names.
          continue;
        default:
          // Look for more extensions in subdirectories.
          process_candidate_dir($fileinfo->getPathname());
      }
    }
    else {
      // It's a file.
      if (preg_match('/^(.+).info.yml$/', $fileinfo->getFilename(), $m)) {
        // It's a *.info.yml file, so we found an extension directory.
        $extension_name = $m[1];
      }
    }
  }
  if (isset($extension_name)) {
    process_extension($extension_name, $dir);
    process_extension_phpunit($extension_name, $dir);
  }
}

/**
 * Process a Drupal extension (module, theme) in a directory.
 *
 * This will move all class files in this extension two levels up, from
 * lib/Drupal/$name/ to lib/.
 *
 * @param string $name
 *   Name of the extension.
 * @param string $dir
 *   Directory of the extension.
 */
function process_extension($name, $dir) {

  if (!is_dir($src = "$dir/lib/Drupal/$name")) {
    // Nothing to do in this module.
    return;
  }

  // Move class files two levels up.
  move_directory_contents($src, "$dir/lib");

  // Clean up.
  require_dir_empty("$dir/lib/Drupal");
  rmdir("$dir/lib/Drupal");
}

/**
 * Process a Drupal extension (module, theme) in a directory.
 *
 * This will move all PHPUnit class files in this extension from
 * tests/Drupal/$name/Tests/ to tests/lib/.
 *
 * @param string $name
 *   Name of the extension.
 * @param string $dir
 *   Directory of the extension.
 */
function process_extension_phpunit($name, $dir) {

  if (!is_dir($src = "$dir/tests/Drupal/$name/Tests")) {
    // Nothing to do in this module.
    return;
  }

  if (!is_dir($dest = "$dir/tests/lib")) {
    mkdir($dest);
  }

  // Move class files two levels up.
  move_directory_contents($src, $dest);

  // Clean up.
  require_dir_empty("$dir/tests/Drupal/$name");
  rmdir("$dir/tests/Drupal/$name");
  require_dir_empty("$dir/tests/Drupal");
  rmdir("$dir/tests/Drupal");
}

/**
 * Move directory contents from an existing source directory to an existing
 * destination directory.
 *
 * @param string $source
 *   An existing source directory.
 * @param string $destination
 *   An existing destination directory.
 *
 * @throws \Exception
 */
function move_directory_contents($source, $destination) {

  if (!is_dir($source)) {
    throw new \Exception("The source '$source' is not a directory.");
  }

  if (!is_dir($destination)) {
    throw new \Exception("The destination '$destination' is not a directory.");
  }

  /**
   * @var \DirectoryIterator $fileinfo
   */
  foreach (new \DirectoryIterator($source) as $fileinfo) {
    if ($fileinfo->isDot()) {
      continue;
    }
    $dest_path = $destination . '/' . $fileinfo->getFilename();
    if (!file_exists($dest_path)) {
      rename($fileinfo->getPathname(), $dest_path);
    }
    elseif ($fileinfo->isFile()) {
      throw new \Exception("Destination '$dest_path' already exists, cannot overwrite.");
    }
    elseif ($fileinfo->isDir()) {
      if (!is_dir($dest_path)) {
        throw new \Exception("Destination '$dest_path' is not a directory.");
      }
      move_directory_contents($fileinfo->getPathname(), $dest_path);
    }
  }

  require_dir_empty($source);

  rmdir($source);
}

/**
 * Throws an exception if a directory is not empty.
 *
 * @param string $dir
 *   Directory to check.
 *
 * @throws \Exception
 */
function require_dir_empty($dir) {
  if (is_file($dir)) {
    throw new \Exception("The path '$dir' is a file, when it should be a directory.");
  }
  if (!is_dir($dir)) {
    throw new \Exception("The directory '$dir' does not exist.");
  }
  if (!is_readable($dir)) {
    throw new \Exception("The directory '$dir' is not readable.");
  }
  /**
   * @var \DirectoryIterator $fileinfo
   */
  foreach (new \DirectoryIterator($dir) as $fileinfo) {
    if ($fileinfo->isDot()) {
      continue;
    }
    $path = $fileinfo->getPathname();
    if ($fileinfo->isFile()) {
      throw new \Exception("File '$path' found in a directory that should be empty.");
    }
    elseif ($fileinfo->isDir()) {
      throw new \Exception("Subdirectory '$path' found in a directory that should be empty.");
    }
  }
}
