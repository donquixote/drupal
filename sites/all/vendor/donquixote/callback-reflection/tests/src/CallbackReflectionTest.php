<?php

namespace Donquixote\CallbackReflection\Tests;

use Donquixote\CallbackReflection\Callback\CallbackReflection_ClassConstruction;

class CallbackReflectionTest extends \PHPUnit_Framework_TestCase {

  public function testClassNameCandidate() {
    $reflectionMethod = new \ReflectionMethod(CallbackReflectionTest_C::class, '__construct');
    $callbackReflection = CallbackReflection_ClassConstruction::createFromClassNameCandidate(CallbackReflectionTest_C::class);

    static::assertSame(
      $php = <<<'EOT'
new \Donquixote\CallbackReflection\Tests\CallbackReflectionTest_C(
  'A
B',
  new \stdClass)
EOT
      ,
      $callbackReflection->argsPhpGetPhp(
        array(
          var_export("A\nB", TRUE),
          'new \stdClass',
        ),
        '  '));

    static::assertSame(
      <<<'EOT'
new \Donquixote\CallbackReflection\Tests\CallbackReflectionTest_C(
  new \Donquixote\CallbackReflection\Tests\CallbackReflectionTest_C(
    'A
B',
    new \stdClass))
EOT
      ,
      $callbackReflection->argsPhpGetPhp(
        array(
          $php,
        ),
        '  '));

    static::assertEquals(
      $reflectionMethod->getParameters(),
      $callbackReflection->getReflectionParameters());

    static::assertEquals(
      new CallbackReflectionTest_C(4, new \stdClass()),
      $callbackReflection->invokeArgs(array(4, new \stdClass())));

    static::assertEquals(
      new CallbackReflectionTest_C("A\nB", new \stdClass()),
      eval('return ' . $php . ';'));
  }

}

class CallbackReflectionTest_C {

  /**
   * @var mixed
   */
  private $x;

  /**
   * @var mixed
   */
  private $y;

  /**
   * @param mixed $x
   * @param mixed $y
   */
  function __construct($x, $y) {
    $this->x = $x;
    $this->y = $y;
  }

}
