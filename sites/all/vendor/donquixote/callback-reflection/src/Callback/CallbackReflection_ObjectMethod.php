<?php

namespace Donquixote\CallbackReflection\Callback;

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
   * @param object $object
   * @param string $methodName
   *
   * @return \Donquixote\CallbackReflection\Callback\CallbackReflection_ObjectMethod
   */
  static function create($object, $methodName) {
    if (!is_object($object)) {
      throw new \InvalidArgumentException("First parameter must be an object.");
    }
    $reflObject = new \ReflectionObject($object);
    if (!$reflObject->hasMethod($methodName)) {
      throw new \InvalidArgumentException("Object has no such method.");
    }
    $reflMethod = $reflObject->getMethod($methodName);
    return new self($object, $reflMethod);
  }

  /**
   * @param object $object
   * @param \ReflectionMethod $reflMethod
   *
   * @throws \InvalidArgumentException
   */
  function __construct($object, \ReflectionMethod $reflMethod) {
    if (!$object instanceof $reflMethod->class) {
      if (!is_object($object)) {
        throw new \InvalidArgumentException("First parameter must be an object.");
      }
      throw new \InvalidArgumentException("Object is not of the required class.");
    }
    $this->object = $object;
    $this->reflMethod = $reflMethod;
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
}
