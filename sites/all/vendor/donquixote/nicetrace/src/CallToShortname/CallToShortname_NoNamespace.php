<?php

namespace Donquixote\Nicetrace\CallToShortname;

class CallToShortname_NoNamespace extends CallToShortnameBase {

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
    if (FALSE !== $nspos = strrpos($class, '\\')) {
      $class = substr($class, $nspos + 1);
    }
    return $class . $call['type'] . $function . '()';
  }

}
