<?php

namespace Donquixote\CallbackReflection\CodegenHelper;

interface CodegenHelperInterface {

  /**
   * @param mixed $value
   *
   * @return string
   */
  public function export($value);

  /**
   * @param object $object
   *
   * @return string
   */
  public function exportObject($object);

  /**
   * @param \Closure $closure
   *
   * @return string
   */
  public function exportClosure(\Closure $closure);

}
