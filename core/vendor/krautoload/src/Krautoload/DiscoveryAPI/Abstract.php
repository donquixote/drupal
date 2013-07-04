<?php

namespace Krautoload;

abstract class DiscoveryAPI_Abstract implements DiscoveryAPI_Interface {

  protected $nsp;

  function setNamespace($namespace) {
    return $this->nsp = $namespace;
  }

  function getNamespace() {
    return $this->nsp;
  }

  function getClassName($relativeClassName) {
    return $this->nsp . $relativeClassName;
  }
}
