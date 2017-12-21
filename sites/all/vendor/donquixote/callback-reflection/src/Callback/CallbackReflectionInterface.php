<?php

namespace Donquixote\CallbackReflection\Callback;

use Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface;

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

  /**
   * @param string[] $argsPhp
   *   PHP statements for each parameter.
   * @param \Donquixote\CallbackReflection\CodegenHelper\CodegenHelperInterface $helper
   *
   * @return string
   *   PHP statement.
   */
  public function argsPhpGetPhp(array $argsPhp, CodegenHelperInterface $helper);

}
