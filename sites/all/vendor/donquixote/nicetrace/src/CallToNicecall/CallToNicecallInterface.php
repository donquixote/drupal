<?php

namespace Donquixote\Nicetrace\CallToNicecall;

interface CallToNicecallInterface {

  /**
   * @param array $call
   *   An item from debug_backtrace()
   *
   * @return array
   */
  function callGetNicecall(array $call);

}
