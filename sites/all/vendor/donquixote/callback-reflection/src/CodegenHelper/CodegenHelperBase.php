<?php

namespace Donquixote\CallbackReflection\CodegenHelper;

use Donquixote\CallbackReflection\Util\CodegenFailureUtil;

abstract class CodegenHelperBase implements CodegenHelperInterface {

  /**
   * @param mixed $value
   *
   * @return string
   */
  public function export($value) {

    if (is_object($value)) {
      return $this->exportObject($value);
    }

    if (is_array($value)) {
      return $this->exportArray($value);
    }

    return var_export($value, TRUE);
  }

  /**
   * @param array $array
   *
   * @return string
   */
  private function exportArray(array $array) {

    if ([] === $array) {
      return '[]';
    }

    $pieces = [];
    if ($array === array_values($array)) {
      foreach ($array as $v) {
        $pieces[] = $this->export($v);
      }
    }
    else {
      foreach ($array as $k => $v) {
        $pieces[] = var_export($k, TRUE) . ' => ' . $this->export($v);
      }
    }

    $php_oneline = implode(', ', $pieces);
    if (FALSE === strpos($php_oneline, "\n") && strlen($php_oneline) < 30) {
      return '[' . $php_oneline . ']';
    }

    return "[\n  " . implode(",\n  ", $pieces) . "\n]";
  }

  /**
   * @param object $object
   *
   * @return string
   */
  public function exportObject($object) {

    if ($object instanceof \Closure) {
      return $this->exportClosure($object);
    }

    $this->addProblem('Exporting objects is not supported.');

    /* @see \Donquixote\CallbackReflection\Util\CodegenFailureUtil::failToCreateObject() */
    return '\\' . CodegenFailureUtil::class . "::failToCreateObject(\\" . get_class($object) . '::class)';
  }

  /**
   * @param \Closure $closure
   *
   * @return string
   */
  public function exportClosure(\Closure $closure) {

    $rf = new \ReflectionFunction($closure);
    $file = basename($rf->getFileName());
    $start_line = $rf->getStartLine();
    $end_line = $rf->getEndLine();

    $this->addProblem('Exporting closures is not supported.');

    $message = "See lines $start_line..$end_line of file \"$file\".";

    /* @see \Donquixote\CallbackReflection\Util\CodegenFailureUtil::failToCreateClosure() */
    return '\\' . CodegenFailureUtil::class . "::failToCreateClosure("
      . "\n  " . var_export($message, TRUE) . ')';
  }

  /**
   * @param string $message
   */
  abstract protected function addProblem($message);

}
