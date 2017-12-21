<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface;

/**
 * A decorator that implements "currying",
 * so some of the parameters are bound while others remain free.
 */
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
   * @var string[]
   */
  private $boundArgsPhp;

  /**
   * @param \Donquixote\CallbackReflection\Callback\CallbackReflectionInterface $decorated
   * @param mixed[] $boundArgs
   *   Arguments for parameters of the decorated callback that should be "bound".
   * @param string[] $boundArgsPhp
   *   PHP equivalent of the arguments, for the argsPhpGetPhp() method.
   *   If $args contains some keys that are not present in $argsPhp, then a
   *   simple var_export() will be attempted.
   */
  function __construct(CallbackReflectionInterface $decorated, array $boundArgs, array $boundArgsPhp = []) {
    $this->decorated = $decorated;
    $this->boundArgs = $boundArgs;
    $this->boundArgsPhp = $boundArgsPhp;
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

  /**
   * @param string[] $argsPhp
   *   PHP statements for each parameter.
   * @param \Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface $helper
   *
   * @return string
   *   PHP statement.
   */
  public function argsPhpGetPhp(array $argsPhp, CodegenHelperInterface $helper) {

    $argsPhp = array_values($argsPhp);

    $j = 0;
    $combinedArgsPhp = [];
    foreach ($this->decorated->getReflectionParameters() as $i => $param) {
      if (array_key_exists($i, $this->boundArgs)) {
        if (array_key_exists($i, $this->boundArgsPhp)) {
          $argPhp = $this->boundArgsPhp[$i];
        }
        else {
          $argPhp = $helper->export($this->boundArgs[$i]);
        }
      }
      elseif (array_key_exists($paramName = $param->getName(), $this->boundArgs)) {
        if (array_key_exists($paramName, $this->boundArgsPhp)) {
          $argPhp = $this->boundArgsPhp[$paramName];
        }
        else {
          $argPhp = $helper->export($this->boundArgs[$paramName]);
        }
      }
      elseif (array_key_exists($j, $argsPhp)) {
        $argPhp = $argsPhp[$j];
        ++$j;
      }
      else {
        throw new \InvalidArgumentException('Insufficient arguments.');
      }
      $combinedArgsPhp[] = $argPhp;
    }

    return $this->decorated->argsPhpGetPhp($combinedArgsPhp, $helper);
  }
}
