<?php

namespace Donquixote\CallbackReflection\Callback;

class CallbackReflection_Closure implements CallbackReflectionInterface {

  /**
   * @var \Closure
   */
  private $closure;

  /**
   * @param \Closure $closure
   */
  function __construct(\Closure $closure) {
    $this->closure = $closure;
  }

  /**
   * Gets the parameters as native \ReflectionParameter objects.
   *
   * @return \ReflectionParameter[]
   *
   * @see \ReflectionFunctionAbstract::getParameters()
   */
  function getReflectionParameters() {
    $reflFunction = new \ReflectionFunction($this->closure);
    return $reflFunction->getParameters();
  }

  /**
   * @param mixed[] $args
   *
   * @return mixed|null
   */
  function invokeArgs(array $args) {
    return call_user_func_array($this->closure, $args);
  }
}
