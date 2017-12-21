<?php

namespace Donquixote\CallbackReflection\Callback;

interface CallbackReflectionInterface {

  /**
   * Gets the parameters as native \ReflectionParameter objects.
   *
   * @return \ReflectionParameter[]
   *
   * @see \ReflectionFunctionAbstract::getParameters()
   */
  function getReflectionParameters();

  /**
   * @param mixed[] $args
   *
   * @return mixed|void
   */
  function invokeArgs(array $args);

}
