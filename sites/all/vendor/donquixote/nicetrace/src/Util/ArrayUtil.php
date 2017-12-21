<?php

namespace Donquixote\Nicetrace\Util;

final class ArrayUtil extends UtilBase {

  /**
   * @param array $array
   * @param string $key
   *
   * @return mixed|null
   */
  static function arrayValueOrNull(array $array, $key) {
    return array_key_exists($key, $array)
      ? $array[$key]
      : NULL;
  }

  /**
   * @param array $array
   * @param string|int $key
   *
   * @return bool
   */
  static function arrayValueNotNull(array $array, $key) {
    return array_key_exists($key, $array) && NULL !== $array[$key];
  }

  /**
   * @param array $array
   * @param string $key
   *
   * @return array
   */
  static function subOrEmptyArray(array $array, $key) {
    if (!array_key_exists($key, $array)) {
      return array();
    }
    if (!is_array($array[$key])) {
      throw new \InvalidArgumentException("Value at key '$key' is expected to be an array.");
    }
    return $array[$key];
  }
}
