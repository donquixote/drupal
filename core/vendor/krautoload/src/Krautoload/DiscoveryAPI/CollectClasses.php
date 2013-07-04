<?php

namespace Krautoload;

class DiscoveryAPI_CollectClasses extends DiscoveryAPI_Abstract {

  protected $classes = array();

  function getCollectedClasses() {
    return $this->classes;
  }

  function fileWithClass($file, $relativeClassName) {
    $this->classes[$this->getClassName($relativeClassName)] = TRUE;
  }

  function fileWithClassCandidates($file, $relativeClassNames) {
    foreach ($relativeClassNames as $relativeClassName) {
      $this->classes[$this->getClassName($relativeClassName)] = TRUE;
    }
  }
}
