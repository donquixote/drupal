<?php

namespace Donquixote\Nicetrace\BacktraceToNicetrace;

interface BacktraceToNicetraceInterface {

  /**
   * @param array $backtrace
   *   A backtrace, as obtained with debug_backtrace().
   *
   * @return array
   *
   * @see debug_backtrace()
   */
  function backtraceGetNicetrace(array $backtrace);

}
