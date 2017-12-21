<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface;
use Donquixote\CallbackReflection\Util\CodegenUtil;

class CallbackReflection_ObjectMethod implements CallbackReflectionInterface {

  /**
   * @var object
   */
  private $object;

  /**
   * @var \ReflectionMethod
   */
  private $reflMethod;

  /**
   * @var null|string
   */
  private $objectPhp;

  /**
   * @param object $object
   * @param string $methodName
   * @param string|null $objectPhp
   *   PHP expression that creates or returns the object, if available.
   *   Otherwise, argsPhpGetPhp() will attempt to export the object.
   *
   * @return \Donquixote\CallbackReflection\Callback\CallbackReflection_ObjectMethod
   */
  static function create($object, $methodName, $objectPhp = NULL) {
    if (!is_object($object)) {
      throw new \InvalidArgumentException("First parameter must be an object.");
    }
    $reflObject = new \ReflectionObject($object);
    if (!$reflObject->hasMethod($methodName)) {
      throw new \InvalidArgumentException("Object has no such method.");
    }
    $reflMethod = $reflObject->getMethod($methodName);
    return new self($object, $reflMethod, $objectPhp);
  }

  /**
   * @param object $object
   * @param \ReflectionMethod $reflMethod
   * @param string|null $objectPhp
   *   PHP expression that creates or returns the object, if available.
   *   Otherwise, argsPhpGetPhp() will attempt to export the object.
   *
   * @throws \InvalidArgumentException
   */
  function __construct($object, \ReflectionMethod $reflMethod, $objectPhp = NULL) {
    if (!$object instanceof $reflMethod->class) {
      if (!is_object($object)) {
        throw new \InvalidArgumentException("First parameter must be an object.");
      }
      throw new \InvalidArgumentException("Object is not of the required class.");
    }
    $this->object = $object;
    $this->reflMethod = $reflMethod;
    $this->objectPhp = $objectPhp;
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
   * @return object|null
   */
  function invokeArgs(array $args) {
    return $this->reflMethod->invokeArgs($this->object, $args);
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

    $objectPhp = (NULL !== $this->objectPhp)
      ? $this->objectPhp
      : $helper->exportObject($this->object);

    $arglistPhp = CodegenUtil::argsPhpGetArglistPhp($argsPhp);

    return $objectPhp
      . "\n  ->" . $this->reflMethod->getName() . '(' . $arglistPhp . ')';
  }
}
