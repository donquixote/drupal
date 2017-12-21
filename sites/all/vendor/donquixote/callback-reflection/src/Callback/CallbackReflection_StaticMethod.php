<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\ArgsPhpToPhp\ArgsPhpToPhpInterface;
use Donquixote\CallbackReflection\Util\CodegenUtil;

class CallbackReflection_StaticMethod implements CallbackReflectionInterface, ArgsPhpToPhpInterface {

  /**
   * @var \ReflectionMethod
   */
  private $reflMethod;

  /**
   * @param \ReflectionMethod $reflMethod
   */
  function __construct(\ReflectionMethod $reflMethod) {
    $this->reflMethod = $reflMethod;
  }

  /**
   * @param string $className
   * @param string $methodName
   *
   * @return \Donquixote\CallbackReflection\Callback\CallbackReflection_StaticMethod
   */
  static function create($className, $methodName) {
    $reflectionMethod = new \ReflectionMethod($className, $methodName);
    return new self($reflectionMethod);
  }

  /**
   * @return \ReflectionParameter[]
   */
  function getReflectionParameters() {
    return $this->reflMethod->getParameters();
  }

  /**
   * @param mixed[] $args
   *
   * @return mixed|null
   */
  function invokeArgs(array $args) {
    return $this->reflMethod->invokeArgs(NULL, $args);
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
    return '\\' . $this->reflMethod->getDeclaringClass()->getName() . '::' . $this->reflMethod->getName() . '(' . $arglistPhp . ')';
  }
}
