<?php

namespace Donquixote\Nicetrace\CallToNicecall;

use Donquixote\Nicetrace\Util\ArrayUtil;

class CallToNicecall_Class implements CallToNicecallInterface {

  /**
   * @param array $call
   *   An item from debug_backtrace()
   *
   * @return array
   */
  function callGetNicecall(array $call) {
    if (NULL === $class = ArrayUtil::arrayValueOrNull($call, 'class')) {
      return array();
    }
    return array('class' => $class);
  }
}
