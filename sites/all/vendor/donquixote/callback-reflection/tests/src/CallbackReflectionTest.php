<?php

namespace Donquixote\CallbackReflection\Tests;

use Donquixote\CallbackReflection\Callback\CallbackReflection_BoundParameters;
use Donquixote\CallbackReflection\Callback\CallbackReflection_ClassConstruction;
use Donquixote\CallbackReflection\Callback\CallbackReflection_Closure;
use Donquixote\CallbackReflection\Callback\CallbackReflection_Function;
use Donquixote\CallbackReflection\Callback\CallbackReflection_ObjectMethod;
use Donquixote\CallbackReflection\Callback\CallbackReflection_StaticMethod;
use Donquixote\CallbackReflection\Callback\CallbackReflectionInterface;
use Donquixote\CallbackReflection\CodegenHelper\CodegenHelper;

class CallbackReflectionTest extends \PHPUnit_Framework_TestCase {

  public function testGetParameters() {
    $reflectionMethod = new \ReflectionMethod($c = CallbackReflectionTest_C::class, '__construct');
    $callbackReflection = CallbackReflection_ClassConstruction::createFromClassNameCandidate($c);
    static::assertEquals(
      $reflectionMethod->getParameters(),
      $callbackReflection->getReflectionParameters());
  }

  /**
   * @param \Donquixote\CallbackReflection\Callback\CallbackReflectionInterface $callbackReflection
   * @param array $args
   * @param mixed $expectedValue
   * @param array $argsPhp
   * @param string $expectedPhp
   * @param string[] $expectedProblems
   *
   * @dataProvider providerTestCallbackReflection()
   */
  public function testCallbackReflection(
    CallbackReflectionInterface $callbackReflection,
    array $args, $expectedValue,
    array $argsPhp, $expectedPhp, array $expectedProblems = []
  ) {
    static::assertEquals($expectedValue, $callbackReflection->invokeArgs($args));

    $helper = new CodegenHelper();
    static::assertSame($expectedPhp, $callbackReflection->argsPhpGetPhp($argsPhp, $helper));
    static::assertSame($expectedProblems, $helper->getProblems());

    try {
      $evalValue = eval('return ' . $expectedPhp . ';');
      static::assertEquals($expectedValue, $evalValue);
      static::assertEmpty($expectedProblems);
    }
    catch(\Exception $e) {
      static::assertNotEmpty($expectedProblems);
    }
  }

  /**
   * @return array[]
   *   Format: $[$name] = [$callbackReflection, $args, $expectedValue, $argsPhp, $expectedPhp]
   */
  public function providerTestCallbackReflection() {
    $argss = [];

    $argss['class'] = [
      $callback = CallbackReflection_ClassConstruction::create($c = CallbackReflectionTest_C::class),
      ["A\nB", new \stdClass()], $v = new CallbackReflectionTest_C("A\nB", new \stdClass()),
      'argsPhp' => [var_export("A\nB", TRUE), 'new \stdClass'], $php = <<<'EOT'
new \Donquixote\CallbackReflection\Tests\CallbackReflectionTest_C(
'A
B',
new \stdClass)
EOT
      ,
    ];

    $argss['class nested'] = [
      CallbackReflection_ClassConstruction::create($c = CallbackReflectionTest_C::class),
      [$v, null], new CallbackReflectionTest_C($v, null),
      [$php, 'null'], <<<'EOT'
new \Donquixote\CallbackReflection\Tests\CallbackReflectionTest_C(
new \Donquixote\CallbackReflection\Tests\CallbackReflectionTest_C(
'A
B',
new \stdClass),
null)
EOT
      ,
    ];

    $argss['closure'] = [
      new CallbackReflection_Closure(include dirname(__DIR__) . '/fixtures/closure.php'),
      ['A', 'B'], 'ok',
      ["'A'", "'B'"], <<<'EOT'
call_user_func_array(
\Donquixote\CallbackReflection\Util\CodegenFailureUtil::failToCreateClosure(
  'See lines 3..5 of file "closure.php".'),
'A',
'B')
EOT
      , ['Exporting closures is not supported.'],
    ];

    $argss['function native'] = [
      CallbackReflection_Function::create('json_encode'),
      [new \stdClass()], '{}',
      ['new \stdClass()'], '\json_encode(new \stdClass())',
    ];

    $argss['object method'] = [
      CallbackReflection_ObjectMethod::create(new CallbackReflectionTest_C('X', 'Y'), 'foo'),
      ['Z'], ['XZ', 'YZ'],
      ["'Z'"], <<<EOT
\Donquixote\CallbackReflection\Util\CodegenFailureUtil::failToCreateObject(\Donquixote\CallbackReflection\Tests\CallbackReflectionTest_C::class)
  ->foo('Z')
EOT
      , ['Exporting objects is not supported.'],
    ];

    $argss['static method'] = [
      CallbackReflection_StaticMethod::create($c, 'fooStatic'),
      ['Z'], 'Zz',
      ["'Z'"], "\\$c::fooStatic('Z')",
    ];

    $argss['bound parameters'] = [
      new CallbackReflection_BoundParameters($callback, ['X'], ["'X'"]),
      ['Y'], new CallbackReflectionTest_C('X', 'Y'),
      ["'Y'"], "new \\$c('X', 'Y')",
    ];

    return $argss;
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
   * @param string $z
   *
   * @return string
   */
  static function fooStatic($z) {
    return strtoupper($z) . strtolower($z);
  }

  /**
   * @param mixed $x
   * @param mixed $y
   */
  function __construct($x, $y) {
    $this->x = $x;
    $this->y = $y;
  }

  function foo($z) {
    return [$this->x . $z, $this->y . $z];
  }

}
