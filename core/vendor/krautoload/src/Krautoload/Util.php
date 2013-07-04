<?php

namespace Krautoload;

class Util {

  /**
   * Determine if the class loader is called in a context where not loading a
   * class is "non-lethal".
   */
  static function calledFromClassExists() {
    foreach ($trace = debug_backtrace() as $i => $item) {
      if ($item['function'] === 'spl_autoload_call') {
        switch ($f = $trace[$i + 1]['function']) {
          case 'class_exists':
          case 'interface_exists':
          case 'method_exists':
          case 'is_callable':
          // @todo Add more cases.
            return TRUE;
          default:
            echo "FUNCTION: $f\n";
            return FALSE;
        }
      }
    }
  }
}
