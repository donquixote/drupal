<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface;
use Donquixote\CallbackReflection\Util\CodegenUtil;

/**
 * Wraps a class constructor as a factory callback.
 */
class CallbackReflection_ClassConstruction implements CallbackReflectionInterface {

  /**
   * @var \ReflectionClass
   */
  private $reflClass;

  /**
   * @param string $class
   *
   * @return null|static
   */
  static function createFromClassNameCandidate($class) {
    return class_exists($class)
      ? new static(new \ReflectionClass($class))
      : NULL;
  }

  /**
   * @param string $class
   *
   * @return static
   */
  public static function create($class) {
    return new static(new \ReflectionClass($class));
  }

  /**
   * @param string $class
   *
   * @return static
   */
  static function createFromClassName($class) {
    return new static(new \ReflectionClass($class));
  }

  /**
   * @param \ReflectionClass $reflClass
   */
  function __construct(\ReflectionClass $reflClass) {
    if (!$reflClass->isInstantiable()) {
      throw new \InvalidArgumentException("The class is not instantiable.");
    }
    $this->reflClass = $reflClass;
  }

  /**
   * @return \ReflectionParameter[]
   */
  function getReflectionParameters() {
    $reflConstructor = $this->reflClass->getConstructor();
    if (NULL === $reflConstructor) {
      return array();
    }
    return $reflConstructor->getParameters();
  }

  /**
   * @param mixed[] $args
   *
   * @return object
   */
  function invokeArgs(array $args) {
    return $this->reflClass->newInstanceArgs($args);
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
    return 'new \\' . $this->reflClass->getName() . '(' . $arglistPhp . ')';
  }
}
