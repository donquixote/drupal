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

/**
 * @param string $dir
 *   A directory that could contain Drupal extensions (modules, themes) at the
 *   top level or further down the hierarchy.
 */
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

/**
 * @param string $dir
 *   A directory that could be a Drupal extension directory.
 */
function process_candidate_dir($dir) {
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
  }
}

/**
 * @param string $name
 *   Name of the extension.
 * @param string $dir
 *   Directory of the extension.
 */
function process_extension($name, $dir) {

  if (!is_dir("$dir/lib/Drupal/$name")) {
    // Nothing to move.
    return;
  }

  foreach (scandir("$dir/lib/Drupal/$name") as $candidate) {
    if ('.' === $candidate || '..' === $candidate) {
      continue;
    }
    if (file_exists("$dir/$candidate")) {
      print "'$dir/$candidate' already exists. Ignoring.\n";
    }
    if (!rename($src = "$dir/lib/Drupal/$name/$candidate", $dest = "$dir/$candidate")) {
      throw new Exception("Rename $src to $dest failed.");
    }
  }

  // Clean up empty directories.
  foreach (array(
    "lib/Drupal/$name",
    'lib/Drupal',
    'lib',
  ) as $subdir) {
    if (!is_dir("$dir/$subdir")) {
      continue;
    }
    if (!is_dir_empty("$dir/$subdir")) {
      throw new Exception("$dir/$subdir is not empty.");
    }
    rmdir("$dir/$subdir");
  }
}

/**
 * @param string $dir
 *   Directory to check.
 *
 * @return bool
 *   TRUE, if the directory is empty.
 */
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
