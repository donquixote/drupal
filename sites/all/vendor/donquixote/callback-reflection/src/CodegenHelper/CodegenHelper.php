<?php

namespace Donquixote\CallbackReflection\CodegenHelper;

class CodegenHelper extends CodegenHelperBase {

  /**
   * @var string[]
   */
  private $problems = [];

  /**
   * @return string[]
   */
  public function getProblems() {
    return $this->problems;
  }

  /**
   * @param string $message
   */
  protected function addProblem($message) {
    $this->problems[] = $message;
  }
}
