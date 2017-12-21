<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface;
use Donquixote\CallbackReflection\Util\CodegenUtil;

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

  /**
   * @param string[] $argsPhp
   *   PHP statements for each parameter.
   * @param \Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface $helper
   *
   * @return string
   *   PHP statement.
   */
  public function argsPhpGetPhp(array $argsPhp, CodegenHelperInterface $helper) {

    // This is going to fail, but that's ok.
    $closurePhp = $helper->exportClosure($this->closure);

    array_unshift($argsPhp, $closurePhp);
    $arglistPhp = CodegenUtil::argsPhpGetArglistPhp($argsPhp);
    return 'call_user_func_array(' . $arglistPhp . ')';
  }
}
