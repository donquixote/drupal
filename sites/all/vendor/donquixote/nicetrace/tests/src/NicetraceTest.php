<?php

namespace Donquixote\Nicetrace\Tests;

use Donquixote\Nicetrace\BacktraceToNicetrace\Builder\BacktraceToNicetraceBuilder;
use Donquixote\Nicetrace\Util\NicetraceUtil;

class NicetraceTest extends \PHPUnit_Framework_TestCase {

  function testNicetrace() {

    $backtrace = include dirname(__DIR__) . '/fixtures/backtrace.php';
    $expectedNicetrace = include dirname(__DIR__) . '/fixtures/nicetrace.php';

    $nicetrace = NicetraceUtil::backtraceGetNicetrace($backtrace);

    // Compare array contents.
    static::assertEquals($expectedNicetrace, $nicetrace);

    // Compare array contents and order of (nested) keys.
    static::assertEquals(var_export($expectedNicetrace, TRUE), var_export($nicetrace, TRUE));
  }

  function testNicetraceWithNamespacelessKey() {

    $backtrace = include dirname(__DIR__) . '/fixtures/backtrace.php';
    $expectedNicetrace = include dirname(__DIR__) . '/fixtures/nicetrace.namespaceless.php';

    $backtraceToNicetrace = BacktraceToNicetraceBuilder::start()
      ->withNamespacelessKey()
      ->create();

    $nicetrace = $backtraceToNicetrace->backtraceGetNicetrace($backtrace);

    // Compare array contents.
    static::assertEquals($expectedNicetrace, $nicetrace);

    // Compare array contents and order of (nested) keys.
    static::assertEquals(var_export($expectedNicetrace, TRUE), var_export($nicetrace, TRUE));
  }

  function testNicetraceWithClasslessKey() {

    $backtrace = include dirname(__DIR__) . '/fixtures/backtrace.php';
    $expectedNicetrace = include dirname(__DIR__) . '/fixtures/nicetrace.classless.php';

    $backtraceToNicetrace = BacktraceToNicetraceBuilder::start()
      ->withClasslessKey()
      ->create();

    $nicetrace = $backtraceToNicetrace->backtraceGetNicetrace($backtrace);

    // Compare array contents.
    static::assertEquals($expectedNicetrace, $nicetrace);

    // Compare array contents and order of (nested) keys.
    static::assertEquals(var_export($expectedNicetrace, TRUE), var_export($nicetrace, TRUE));
  }

  function testNicetraceWithBasePaths() {

    $backtrace = include dirname(__DIR__) . '/fixtures/backtrace.php';
    $expectedNicetrace = include dirname(__DIR__) . '/fixtures/nicetrace.basepaths.php';

    $backtraceToNicetrace = BacktraceToNicetraceBuilder::start()
      ->withBasePaths(array('/home/lemonhead/projects/phplib/nicetrace/'))
      ->create();

    $nicetrace = $backtraceToNicetrace->backtraceGetNicetrace($backtrace);

    // Compare array contents.
    static::assertEquals($expectedNicetrace, $nicetrace);

    // Compare array contents and order of (nested) keys.
    static::assertEquals(var_export($expectedNicetrace, TRUE), var_export($nicetrace, TRUE));
  }

}
