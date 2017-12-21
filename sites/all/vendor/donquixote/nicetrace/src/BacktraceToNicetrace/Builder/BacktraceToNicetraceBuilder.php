<?php

namespace Donquixote\Nicetrace\BacktraceToNicetrace\Builder;

use Donquixote\Nicetrace\BacktraceToNicetrace\BacktraceToNicetrace;
use Donquixote\Nicetrace\CallToNicecall\CallToNicecall_Class;
use Donquixote\Nicetrace\CallToNicecall\CallToNicecall_FileWithLine;
use Donquixote\Nicetrace\CallToNicecall\CallToNicecall_MultipleCombined;
use Donquixote\Nicetrace\CallToNicecall\CallToNicecall_NamedArgs;
use Donquixote\Nicetrace\CallToShortname\CallToShortname_Full;
use Donquixote\Nicetrace\CallToShortname\CallToShortname_NoClass;
use Donquixote\Nicetrace\CallToShortname\CallToShortname_NoNamespace;
use Donquixote\Nicetrace\PathShortener\PathShortener_BasePaths;
use Donquixote\Nicetrace\PathShortener\PathShortener_Passthru;

/**
 * In this default implementation, methods return a modified clone of the
 * builder, instead of modifying the builder itself. This means the builder,
 * like everything in nicetrace, is immutable (=== stateless).
 */
class BacktraceToNicetraceBuilder implements BacktraceToNicetraceBuilderInterface {

  /**
   * @var \Donquixote\Nicetrace\PathShortener\PathShortener_Passthru
   */
  private $pathShortener;

  /**
   * @var \Donquixote\Nicetrace\CallToShortname\CallToShortnameInterface
   */
  private $callToShortname;

  /**
   * @var bool
   */
  private $withClass = FALSE;

  /**
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  static function start() {
    return new self();
  }

  /**
   * The constructor.
   */
  function __construct() {
    $this->callToShortname = new CallToShortname_Full();
    $this->pathShortener = new PathShortener_Passthru();
  }

  /**
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withClasslessKey() {
    $clone = clone $this;
    $clone->callToShortname = new CallToShortname_NoClass();
    // Since the FQCN is no longer in the key, add it in the info array for each call.
    $clone->withClass = TRUE;
    return $clone;
  }

  /**
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withNamespacelessKey() {
    $clone = clone $this;
    $clone->callToShortname = new CallToShortname_NoNamespace();
    // Since the FQCN is no longer in the key, add it in the info array for each call.
    $clone->withClass = TRUE;
    return $clone;
  }

  /**
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withClass() {
    $clone = clone $this;
    $clone->withClass = TRUE;
    return $clone;
  }

  /**
   * @param string[] $basePaths
   *
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withBasePaths(array $basePaths) {
    $clone = clone $this;
    $clone->pathShortener = new PathShortener_BasePaths($basePaths);
    return $clone;
  }

  /**
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\BacktraceToNicetraceInterface
   */
  function create() {
    $innerCallToNicecall = new CallToNicecall_FileWithLine($this->pathShortener);
    $outerCallToNicecall = new CallToNicecall_NamedArgs();
    if ($this->withClass) {
      $outerCallToNicecall = new CallToNicecall_MultipleCombined(
        array(
          new CallToNicecall_Class(),
          $outerCallToNicecall,
        ));
    }
    return new BacktraceToNicetrace($innerCallToNicecall, $outerCallToNicecall, $this->callToShortname);
  }

}
