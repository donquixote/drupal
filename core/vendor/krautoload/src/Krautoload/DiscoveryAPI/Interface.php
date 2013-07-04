<?php

namespace Krautoload;

interface DiscoveryAPI_Interface {

  function setNamespace($namespace);

  function getNamespace();

  function fileWithClass($file, $relativeClassName);

  function fileWithClassCandidates($file, $relativeClassNames);
}
