<?php

namespace Krautoload;

class FinderPlugin_PSRX implements FinderPlugin_Interface {

  function pluginFindFile($api, $prefix, $dir, $suffix) {
    if ($api->guessFile($dir . $suffix)) {
      return TRUE;
    }
  }

  function pluginLoadClass($class, $prefix, $dir, $suffix) {
    if (is_file($file = $dir . $suffix)) {
      include $file;
      return TRUE;
    }
  }
}
