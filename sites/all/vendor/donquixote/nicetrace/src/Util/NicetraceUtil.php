<?php

namespace Donquixote\Nicetrace\Util;

use Donquixote\Nicetrace\BacktraceToNicetrace\BacktraceToNicetrace;

final class NicetraceUtil extends UtilBase {

  /**
   * @param array[] $backtrace
   *   A backtrace, as obtained with debug_backtrace(TRUE).
   * @param string[] $basePaths
   *
   * @return array[]
   */
  static function backtraceGetNicetrace(array $backtrace, array $basePaths = array()) {
    $backtraceToNicetrace = BacktraceToNicetrace::createWithBasePaths($basePaths);
    return $backtraceToNicetrace->backtraceGetNicetrace($backtrace);
  }

}
