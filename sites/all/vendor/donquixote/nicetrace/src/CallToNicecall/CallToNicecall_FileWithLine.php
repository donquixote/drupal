<?php

namespace Donquixote\Nicetrace\CallToNicecall;

use Donquixote\Nicetrace\PathShortener\PathShortenerInterface;
use Donquixote\Nicetrace\Util\ArrayUtil;

class CallToNicecall_FileWithLine implements CallToNicecallInterface {

  /**
   * @var \Donquixote\Nicetrace\PathShortener\PathShortenerInterface
   */
  private $pathShortener;

  /**
   * @param \Donquixote\Nicetrace\PathShortener\PathShortenerInterface $pathShortener
   */
  function __construct(PathShortenerInterface $pathShortener) {
    $this->pathShortener = $pathShortener;
  }

  /**
   * @param array $call
   *   An item from debug_backtrace()
   *
   * @return array
   */
  function callGetNicecall(array $call) {
    $nicecall = array();
    if (NULL !== $file = ArrayUtil::arrayValueOrNull($call, 'file')) {
      $nicecall[basename($file) . ': ' . $call['line']] = $this->pathShortener->pathGetShortenedPath($file);
    }
    return $nicecall;
  }
}
