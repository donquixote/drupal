<?php

namespace Krautoload;

class FinderPlugin_ShallowPSR0 implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($suffix, DIRECTORY_SEPARATOR)) {
      $suffix = substr($suffix, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($suffix, $pos));
    }
    else {
      $suffix = str_replace('_', DIRECTORY_SEPARATOR, $suffix);
    }

    // We "guess", because we don't know if the file exists.
    // It is a "candidate", because we don't know for sure if the file actually
    // declares the class we are looking for.
    if ($api->guessFileCandidate($dir . $suffix)) {
      return TRUE;
    }
  }

  function pluginLoadClass($class, $prefix, $dir, $suffix) {

    // Replace the underscores after the last directory separator.
    if (FALSE !== $pos = strrpos($suffix, DIRECTORY_SEPARATOR)) {
      $suffix = substr($suffix, 0, $pos) . str_replace('_', DIRECTORY_SEPARATOR, substr($suffix, $pos));
    }
    else {
      $suffix = str_replace('_', DIRECTORY_SEPARATOR, $suffix);
    }

    // Check whether the file exists.
    if (is_file($file = $dir . $suffix)) {
      // We don't know if the file defines the class,
      // and whether it was already included.
      include_once $file;
      return class_exists($class);
    }
  }
}
