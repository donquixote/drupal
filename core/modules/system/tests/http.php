<?php

/**
 * @file
 * Fake an HTTP request, for use during testing.
 */

use Drupal\Core\Test\TestingCoreServices;

chdir('../../../..');

$autoloader = require_once './core/vendor/autoload.php';

// Set a global variable to indicate a mock HTTP request.
$is_http_mock = !empty($_SERVER['HTTPS']);

// Change to HTTP.
$_SERVER['HTTPS'] = NULL;
ini_set('session.cookie_secure', FALSE);
foreach ($_SERVER as &$value) {
  $value = str_replace('core/modules/system/tests/http.php', 'index.php', $value);
  $value = str_replace('https://', 'http://', $value);
}

$core_services = TestingCoreServices::create();

$core_services->exitIfNoTest();

$core_services->CoreRequestHandler->handleRequestAndExit();
