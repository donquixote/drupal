<?php

namespace Donquixote\CallbackReflection\Callback;

class CallbackReflection_BoundParameters implements CallbackReflectionInterface {

  /**
   * @var \Donquixote\CallbackReflection\Callback\CallbackReflectionInterface
   */
  private $decorated;

  /**
   * @var array
   */
  private $boundArgs;

  /**
   * @param \Donquixote\CallbackReflection\Callback\CallbackReflectionInterface $decorated
   * @param array $args
   */
  function __construct(CallbackReflectionInterface $decorated, array $args) {
    $this->decorated = $decorated;
    $this->boundArgs = $args;
  }

  /**
   * @return \ReflectionParameter[]
   */
  function getReflectionParameters() {
    $params = array();
    foreach ($this->decorated->getReflectionParameters() as $i => $param) {
      if (!array_key_exists($i, $this->boundArgs) && !array_key_exists($param->getName(), $this->boundArgs)) {
        $params[] = $param;
      }
    }
    return $params;
  }

  /**
   * @param mixed[] $args
   *
   * @return mixed
   */
  function invokeArgs(array $args) {
    $args = array_values($args);
    $j = 0;
    $combinedArgs = array();
    foreach ($this->decorated->getReflectionParameters() as $i => $param) {
      if (array_key_exists($i, $this->boundArgs)) {
        $arg = $this->boundArgs[$i];
      }
      elseif (array_key_exists($param->getName(), $this->boundArgs)) {
        $arg = $this->boundArgs[$param->getName()];
      }
      elseif (array_key_exists($j, $args)) {
        $arg = $args[$j];
        ++$j;
      }
      else {
        throw new \InvalidArgumentException('Insufficient arguments.');
      }
      $combinedArgs[] = $arg;
    }
    return $this->decorated->invokeArgs($combinedArgs);
  }
}
