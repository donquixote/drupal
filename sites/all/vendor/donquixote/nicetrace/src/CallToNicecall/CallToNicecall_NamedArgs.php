<?php

namespace Donquixote\Nicetrace\CallToNicecall;

use Donquixote\Nicetrace\Util\ArrayUtil;

class CallToNicecall_NamedArgs implements CallToNicecallInterface {

  /**
   * @param array $call
   *   An item from debug_backtrace()
   *
   * @return array
   */
  function callGetNicecall(array $call) {

    $paramNames = $this->callGetParamNames($call);

    $nicecall = array();

    foreach (ArrayUtil::subOrEmptyArray($call, 'args') as $i => $value) {
      if (array_key_exists($i, $paramNames)) {
        $nicecall['args[' . $i . ']: $' . $paramNames[$i]] = $value;
      }
      else {
        $nicecall['args[' . $i . ']'] = $value;
      }
    }

    return $nicecall;
  }

  /**
   * @param array $call
   *
   * @return string[]
   */
  private function callGetParamNames(array $call) {
    if (NULL === $reflectionFunction = $this->callGetReflectionFunction($call)) {
      return array();
    }
    $names = array();
    foreach ($reflectionFunction->getParameters() as $i => $param) {
      $names[$i] = $param->getName();
    }
    return $names;
  }

  /**
   * @param array $call
   *
   * @return \ReflectionFunctionAbstract|null
   */
  private function callGetReflectionFunction(array $call) {
    $function = $call['function'];
    if (NULL !== $class = ArrayUtil::arrayValueOrNull($call, 'class')) {
      if (method_exists($class, $function)) {
        return new \ReflectionMethod($class, $function);
      }
    }
    elseif (function_exists($function)) {
      return new \ReflectionFunction($function);
    }
    return NULL;
  }
}
