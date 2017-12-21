<?php

namespace Donquixote\CallbackReflection\Util;

use Donquixote\CallbackReflection\Exception\GeneratedCodeException;

final class CodegenFailureUtil extends UtilBase {

  /**
   * @param string $class
   *
   * @throws \Donquixote\CallbackReflection\Exception\GeneratedCodeException
   */
  public static function failToCreateObject($class) {
    throw new GeneratedCodeException("Cannot export object to PHP.");
  }

  /**
   * @param string $message
   *
   * @throws \Donquixote\CallbackReflection\Exception\GeneratedCodeException
   */
  public static function failToCreateClosure($message) {
    throw new GeneratedCodeException($message);
  }

}
