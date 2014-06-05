<?php

/**
 * @file
 * Fake an HTTPS request, for use during testing.
 *
 * @todo Fix this to use a new request rather than modifying server variables,
 *   see http.php.
 */

use Drupal\Core\Test\TestingCoreServices;

chdir('../../../..');

$autoloader = require_once './core/vendor/autoload.php';

// Set a global variable to indicate a mock HTTPS request.
$is_https_mock = empty($_SERVER['HTTPS']);

// Change to HTTPS.
$_SERVER['HTTPS'] = 'on';
foreach ($_SERVER as &$value) {
  $value = str_replace('core/modules/system/tests/https.php', 'index.php', $value);
  $value = str_replace('http://', 'https://', $value);
}

$core_services = TestingCoreServices::create();

$core_services->exitIfNoTest();

$core_services->CoreRequestHandler->handleRequestAndExit();
