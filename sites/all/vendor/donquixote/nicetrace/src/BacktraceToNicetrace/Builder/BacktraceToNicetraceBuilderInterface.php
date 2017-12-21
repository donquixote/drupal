<?php
namespace Donquixote\Nicetrace\BacktraceToNicetrace\Builder;

/**
 * A fluent builder interface for a configured BacktraceToNicetrace handler.
 *
 * @see \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilder
 */
interface BacktraceToNicetraceBuilderInterface {

  /**
   * Keys for method calls should be without the class name.
   * E.g. [' 5: ->foo()'], for \MyNamespace\MyClass->foo()
   *
   * In the default implementation, this implicitly activates "->withClass()".
   *
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withClasslessKey();

  /**
   * Keys for method calls should include the class name, but not the namespace.
   * E.g. [' 5: MyClass->foo()'], for \MyNamespace\MyClass->foo()
   *
   * In the default implementation, this implicitly activates "->withClass()".
   *
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withNamespacelessKey();

  /**
   * Adds an entry 'class' => 'MyNamespace\MyClass' into nicetrace items for
   * method calls.
   *
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withClass();

  /**
   * Specifies base paths for path shortening.
   * This replaces previously specified base paths.
   *
   * @param string[] $basePaths
   *
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilderInterface
   */
  function withBasePaths(array $basePaths);

  /**
   * Creates the actual BacktraceToNicetrace handler.
   *
   * @return \Donquixote\Nicetrace\BacktraceToNicetrace\BacktraceToNicetraceInterface
   */
  function create();
}
