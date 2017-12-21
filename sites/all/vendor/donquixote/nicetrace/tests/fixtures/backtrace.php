<?php

/** @noinspection ImplicitMagicMethodCallInspection */
/** @noinspection PhpUndefinedMethodInspection */
return array (
  0 => 
  array (
    'file' => '/home/lemonhead/projects/phplib/nicetrace/tests/src/makebacktrace.php',
    'line' => 5,
    'function' => 'staticGetBacktrace',
    'class' => 'Donquixote\\Nicetrace\\Tests\\NicetraceTest',
    'type' => '::',
    'args' => 
    array (
    ),
  ),
  1 => 
  array (
    'file' => '/home/lemonhead/projects/phplib/nicetrace/tests/src/NicetraceTest.php',
    'line' => 35,
    'args' => 
    array (
      0 => '/home/lemonhead/projects/phplib/nicetrace/tests/src/makebacktrace.php',
    ),
    'function' => 'include',
  ),
  2 => 
  array (
    'file' => '/home/lemonhead/projects/phplib/nicetrace/tests/src/NicetraceTest.php',
    'line' => 11,
    'function' => 'getBacktrace',
    'class' => 'Donquixote\\Nicetrace\\Tests\\NicetraceTest',
    'type' => '->',
    'args' => 
    array (
      0 => 5,
    ),
  ),
  3 => 
  array (
    'function' => 'testNicetraceExport',
    'class' => 'Donquixote\\Nicetrace\\Tests\\NicetraceTest',
    'type' => '->',
    'args' => 
    array (
    ),
  ),
  4 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/Framework/TestCase.php',
    'line' => 860,
    'function' => 'invokeArgs',
    'class' => 'ReflectionMethod',
    'type' => '->',
    'args' => 
    array (
      0 => '{Donquixote\\Nicetrace\\Tests\\NicetraceTest}',
      1 => 
      array (
      ),
    ),
  ),
  5 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/Framework/TestCase.php',
    'line' => 737,
    'function' => 'runTest',
    'class' => 'PHPUnit_Framework_TestCase',
    'type' => '->',
    'args' => 
    array (
    ),
  ),
  6 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/Framework/TestResult.php',
    'line' => 609,
    'function' => 'runBare',
    'class' => 'PHPUnit_Framework_TestCase',
    'type' => '->',
    'args' => 
    array (
    ),
  ),
  7 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/Framework/TestCase.php',
    'line' => 693,
    'function' => 'run',
    'class' => 'PHPUnit_Framework_TestResult',
    'type' => '->',
    'args' => 
    array (
      0 => '{Donquixote\\Nicetrace\\Tests\\NicetraceTest}',
    ),
  ),
  8 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/Framework/TestSuite.php',
    'line' => 716,
    'function' => 'run',
    'class' => 'PHPUnit_Framework_TestCase',
    'type' => '->',
    'args' => 
    array (
      0 => '{PHPUnit_Framework_TestResult}',
    ),
  ),
  9 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/Framework/TestSuite.php',
    'line' => 716,
    'function' => 'run',
    'class' => 'PHPUnit_Framework_TestSuite',
    'type' => '->',
    'args' => 
    array (
      0 => '{PHPUnit_Framework_TestResult}',
    ),
  ),
  10 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/TextUI/TestRunner.php',
    'line' => 402,
    'function' => 'run',
    'class' => 'PHPUnit_Framework_TestSuite',
    'type' => '->',
    'args' => 
    array (
      0 => '{PHPUnit_Framework_TestResult}',
    ),
  ),
  11 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/TextUI/Command.php',
    'line' => 152,
    'function' => 'doRun',
    'class' => 'PHPUnit_TextUI_TestRunner',
    'type' => '->',
    'args' => 
    array (
      0 => '{PHPUnit_Framework_TestSuite}',
      1 => 
      array (
        'listGroups' => false,
        'loader' => NULL,
        'useDefaultConfiguration' => true,
        'testSuffixes' => 
        array (
          0 => 'Test.php',
          1 => '.phpt',
        ),
        'configuration' => '/home/lemonhead/projects/phplib/nicetrace/phpunit.xml.dist',
      ),
    ),
  ),
  12 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/src/TextUI/Command.php',
    'line' => 104,
    'function' => 'run',
    'class' => 'PHPUnit_TextUI_Command',
    'type' => '->',
    'args' => 
    array (
      0 => 
      array (
        0 => '/home/lemonhead/.composer/vendor/bin/phpunit',
      ),
      1 => true,
    ),
  ),
  13 => 
  array (
    'file' => '/home/lemonhead/.composer/vendor/phpunit/phpunit/phpunit',
    'line' => 36,
    'function' => 'main',
    'class' => 'PHPUnit_TextUI_Command',
    'type' => '::',
    'args' => 
    array (
    ),
  ),
);
