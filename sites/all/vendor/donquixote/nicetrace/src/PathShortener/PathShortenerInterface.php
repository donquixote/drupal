<?php

namespace Donquixote\Nicetrace\PathShortener;

interface PathShortenerInterface {

  /**
   * @param string $path
   *
   * @return string
   */
  function pathGetShortenedPath($path);

}
