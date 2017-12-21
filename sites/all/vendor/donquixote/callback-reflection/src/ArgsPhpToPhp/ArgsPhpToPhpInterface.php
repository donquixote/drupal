<?php

namespace Donquixote\CallbackReflection\ArgsPhpToPhp;

/**
 * Interface for callbacks that support code generation.
 *
 * @see \Donquixote\CallbackReflection\Callback\CallbackReflectionInterface
 */
interface ArgsPhpToPhpInterface {

  /**
   * @param string[] $argsPhp
   *   PHP statements for each parameter.
   * @param string $indentation
   *   E.g. '  '.
   *
   * @return string
   *   PHP statement.
   */
  public function argsPhpGetPhp(array $argsPhp, $indentation);

}
