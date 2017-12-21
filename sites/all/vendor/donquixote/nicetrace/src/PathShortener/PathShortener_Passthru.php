<?php

namespace Donquixote\Nicetrace\PathShortener;

class PathShortener_Passthru implements PathShortenerInterface {

  /**
   * @param string $path
   *
   * @return string
   */
  function pathGetShortenedPath($path) {
    return $path;
  }
}
