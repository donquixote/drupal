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
        case 'src':
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
    if (!is_dir($dir . '/src')) {
      mkdir($dir . '/src');
    }
    rename("$dir/lib/Drupal/$name", "$dir/src");
  }

  // Move class files in tests directory.
  if (is_dir("$dir/tests/Drupal/$name/Tests")) {
    rename("$dir/tests/Drupal/$name/Tests", "$dir/tests/src");
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
    if (is_dir_empty("$dir/$subdir")) {
      rmdir("$dir/$subdir");
    }
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
