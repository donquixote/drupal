#!/bin/php
<?php

/**
 * @file
 * Moves class files in extensions to their PSR-4 location.
 */

// Determine DRUPAL_ROOT.
$dir = dirname(__FILE__);
while (!defined('DRUPAL_ROOT')) {
  if (is_dir($dir . '/core')) {
    define('DRUPAL_ROOT', $dir);
  }
  $dir = dirname($dir);
}

process_extensions_base_dir(DRUPAL_ROOT . '/core/modules');
process_extensions_base_dir(DRUPAL_ROOT . '/core/profiles');

function process_extensions_base_dir($dir) {
  /**
   * @var SplFileInfo $fileinfo
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

function process_candidate_dir($dir) {
  foreach (new \DirectoryIterator($dir) as $fileinfo) {
    if ($fileinfo->isDot()) {
      // Ignore "." and "..".
    }
    elseif ($fileinfo->isDir()) {
      // It's a directory.
      switch ($fileinfo->getFilename()) {
        case 'lib':
        case 'module_autoload_test':
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
  }
}

function process_extension($name, $dir) {

  // Move main module class files.
  if (is_dir("$dir/lib/Drupal/$name")) {
    // This is a module directory with a PSR-0 /lib/ folder.
    // Move to src/ as a temporary location.
    if (!rename($src = "$dir/lib/Drupal/$name", $dest = "$dir/src")) {
      throw new Exception("Rename $src to $dest failed.");
    }
  }

  // Move class files in tests directory.
  if (is_dir("$dir/tests/Drupal/$name/Tests")) {
    // Move to tests/src/ as a temporary location.
    if (!rename($src = "$dir/tests/Drupal/$name/Tests", $dest = "$dir/tests/src")) {
      throw new Exception("Rename $src to $dest failed.");
    }
  }

  // Clean up empty directories.
  foreach (array(
    "lib/Drupal/$name",
    'lib/Drupal',
    'lib',
    "tests/Drupal/$name/Tests",
    "tests/Drupal/$name",
    "tests/Drupal",
  ) as $subdir) {
    if (!is_dir("$dir/$subdir")) {
      continue;
    }
    if (!is_dir_empty("$dir/$subdir")) {
      throw new Exception("$dir/$subdir is not empty.");
    }
    rmdir("$dir/$subdir");
  }

  // Move back to lib/ or tests/lib/.
  if (is_dir("$dir/src")) {
    rename("$dir/src", "$dir/lib");
  }
  if (is_dir("$dir/tests/src")) {
    rename("$dir/tests/src", "$dir/tests/lib");
  }
}

function is_dir_empty($dir) {
  if (!is_readable($dir)) {
    return NULL;
  }
  $handle = opendir($dir);
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != "..") {
      return FALSE;
    }
  }
  return TRUE;
}
