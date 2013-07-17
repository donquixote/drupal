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

process_modules_dir(DRUPAL_ROOT . '/core/modules');
process_modules_dir(DRUPAL_ROOT . '/core/profiles');

function process_modules_dir($dir) {
  /**
   * @var SplFileInfo $fileinfo
   */
  foreach (new \DirectoryIterator($dir) as $fileinfo) {
    if ($fileinfo->isDot()) {
      // do nothing
    }
    elseif ($fileinfo->isDir()) {
      process_module_dir($fileinfo->getPathname());
    }
  }
}

function process_module_dir($dir) {
  $has = array(
    'lib' => FALSE,
    'src' => FALSE,
    'info' => FALSE,
  );
  foreach (new \DirectoryIterator($dir) as $fileinfo) {
    if ($fileinfo->isDot()) {
      // do nothing.
    }
    elseif ($fileinfo->isDir()) {
      if ('lib' === $fileinfo->getFilename()) {
        $has['lib'] = TRUE;
      }
      elseif ('src' === $fileinfo->getFilename()) {
        $has['src'] = TRUE;
      }
      elseif ('module_autoload_test' === $fileinfo->getFilename()) {
        // Do not move anything in this testing module,
        // which already has an src folder.
      }
      else {
        process_module_dir($fileinfo->getPathname());
      }
    }
    else {
      if (preg_match('/^(.+).info.yml$/', $fileinfo->getFilename(), $m)) {
        $extension_name = $m[1];
        $has['info'] = TRUE;
      }
    }
  }
  if ($has['lib'] && $has['info']) {
    if (is_dir("$dir/lib/Drupal/$extension_name")) {
      // This is a module directory with a /lib/ folder.
      if (!$has['src']) {
        mkdir($dir . '/src');
      }
      rename("$dir/lib/Drupal/$extension_name", "$dir/src");
    }
    if (is_dir_empty("$dir/lib/Drupal")) {
      rmdir("$dir/lib/Drupal");
    }
    if (is_dir_empty("$dir/lib")) {
      rmdir("$dir/lib");
    }
  }
}

function is_dir_empty($dir) {
  if (!is_readable($dir)) return NULL;
  $handle = opendir($dir);
  while (false !== ($entry = readdir($handle))) {
    if ($entry != "." && $entry != "..") {
      return FALSE;
    }
  }
  return TRUE;
}
