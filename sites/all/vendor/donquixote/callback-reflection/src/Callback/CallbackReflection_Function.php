<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\ArgsPhpToPhp\ArgsPhpToPhpInterface;
use Donquixote\CallbackReflection\Util\CodegenUtil;

class CallbackReflection_Function implements CallbackReflectionInterface, ArgsPhpToPhpInterface {

  /**
   * @var \ReflectionFunction
   */
  private $reflFunction;

  /**
   * @param \ReflectionFunction $reflFunction
   */
  function __construct(\ReflectionFunction $reflFunction) {
    $this->reflFunction = $reflFunction;
  }

  /**
   * @return \ReflectionParameter[]
   */
  function getReflectionParameters() {
    return $this->reflFunction->getParameters();
  }

  /**
   * @param mixed[] $args
   *
   * @return mixed|null
   */
  function invokeArgs(array $args) {
    return $this->reflFunction->invokeArgs($args);
  }

  /**
   * @param string[] $argsPhp
   *   PHP statements for each parameter.
   * @param string $indentation
   *
   * @return string
   *   PHP statement.
   */
  public function argsPhpGetPhp(array $argsPhp, $indentation) {
    $arglistPhp = CodegenUtil::argsPhpGetArglistPhp($argsPhp, $indentation);
    return '\\' . $this->reflFunction->getName() . '(' . $arglistPhp . ')';
  }
}
