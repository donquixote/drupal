<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface;
use Donquixote\CallbackReflection\Util\CodegenUtil;

class CallbackReflection_Function implements CallbackReflectionInterface {

  /**
   * @var \ReflectionFunction
   */
  private $reflFunction;

  /**
   * @param string $function
   *
   * @return \Donquixote\CallbackReflection\Callback\CallbackReflection_Function
   */
  public static function create($function) {
    return new self(new \ReflectionFunction($function));
  }

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
   * @param \Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface $helper
   *
   * @return string
   *   PHP statement.
   */
  public function argsPhpGetPhp(array $argsPhp, CodegenHelperInterface $helper) {
    $arglistPhp = CodegenUtil::argsPhpGetArglistPhp($argsPhp);
    return '\\' . $this->reflFunction->getName() . '(' . $arglistPhp . ')';
  }
}
