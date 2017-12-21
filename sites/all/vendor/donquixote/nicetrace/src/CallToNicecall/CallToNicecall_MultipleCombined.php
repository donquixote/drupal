<?php

namespace Donquixote\Nicetrace\CallToNicecall;

/**
 * Combines the result from multiple CallToNicecall handlers.
 */
class CallToNicecall_MultipleCombined implements CallToNicecallInterface {

  /**
   * @var \Donquixote\Nicetrace\CallToNicecall\CallToNicecallInterface[]
   */
  private $multiple;

  /**
   * @param \Donquixote\Nicetrace\CallToNicecall\CallToNicecallInterface[] $multiple
   */
  function __construct(array $multiple) {
    $this->multiple = $multiple;
  }

  /**
   * @param array $call
   *   An item from debug_backtrace()
   *
   * @return array
   */
  function callGetNicecall(array $call) {
    $nicecall = array();
    foreach ($this->multiple as $callToNicecall) {
      $nicecall += $callToNicecall->callGetNicecall($call);
    }
    return $nicecall;
  }
}
