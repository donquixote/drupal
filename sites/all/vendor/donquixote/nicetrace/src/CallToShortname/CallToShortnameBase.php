<?php

namespace Donquixote\Nicetrace\CallToShortname;

use Donquixote\Nicetrace\Util\ArrayUtil;

abstract class CallToShortnameBase implements CallToShortnameInterface {

  /**
   * @param array $call
   *
   * @return string
   */
  function callGetShortname(array $call) {
    $function = $call['function'];
    if (NULL !== $class = ArrayUtil::arrayValueOrNull($call, 'class')) {
      return $this->methodCallGetShortname($class, $function, $call);
    }
    elseif ('include' === $function || 'include_once' === $function || 'require' === $function || 'require_once' === $function) {
      return $this->includeGetShortname($function, $call['args'][0], $call);
    }
    else {
      return $this->functionCallGetShortname($function, $call);
    }
  }

  /**
   * @param string $class
   * @param string $function
   * @param array $call
   *   A call array where the existence of $call['class'] and $call['function']
   *   has been verified.
   *
   * @return mixed
   */
  protected function methodCallGetShortname($class, $function, array $call) {
    return $class . $call['type'] . $function . '()';
  }

  /**
   * @param string $function
   * @param array $call
   *
   * @return string
   */
  protected function functionCallGetShortname($function, /** @noinspection PhpUnusedParameterInspection */ array $call) {
    return $function . '()';
  }

  /**
   * @param string $function
   * @param string $includedFile
   * @param array $call
   *
   * @return string
   */
  protected function includeGetShortname($function, $includedFile, /** @noinspection PhpUnusedParameterInspection */ array $call) {
    if ('/' === $includedFile[0]) {
      $includedFileShortname = '/[..]/' . basename($includedFile);
    }
    elseif (FALSE !== strpos($includedFile, '/')) {
      $includedFileShortname = '[..]/' . basename($includedFile);
    }
    else {
      $includedFileShortname = $includedFile;
    }
    return $function . ' ' . $includedFileShortname;
  }
}
