<?php

namespace Donquixote\Nicetrace\CallToShortname;

class CallToShortname_NoClass extends CallToShortnameBase {

  /**
   * @param string $class
   * @param string $function
   * @param array $call
   *   A call array where the existence of $call['class'] and $call['function']
   *   has been verified.
   *
   * @return mixed
   */
  protected function methodCallGetShortname($class, $function, array $call) {
    return $call['type'] . $function . '()';
  }

}
