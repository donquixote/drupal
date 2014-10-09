#!/usr/bin/env php
<?php

/**
 * Drupal hash script - to generate a hash from a plaintext password
 *
 * @param password1 [password2 [password3 ...]]
 *  Plain-text passwords in quotes (or with spaces backslash escaped).
 */

use Drupal\Core\CoreContainer\CoreServices;

if (PHP_SAPI !== 'cli') {
  return;
}

if (version_compare(PHP_VERSION, '5.4.4-14+deb7u14') < 0 && version_compare(PHP_VERSION, '5.4.5') < 0) {
  $version  = PHP_VERSION;
  echo <<<EOF

ERROR: This script requires at least PHP version 5.4.5. You invoked it with
       PHP version {$version}.
\n
EOF;
  exit;
}

$script = basename(array_shift($_SERVER['argv']));

if (in_array('--help', $_SERVER['argv']) || empty($_SERVER['argv'])) {
  echo <<<EOF

Generate Drupal password hashes from the shell.

Usage:        {$script} [OPTIONS] "<plan-text password>"
Example:      {$script} "mynewpassword"

All arguments are long options.

  --help      Print this page.

  "<password1>" ["<password2>" ["<password3>" ...]]

              One or more plan-text passwords enclosed by double quotes. The
              output hash may be manually entered into the
              {users_field_data}.pass field to change a password via SQL to a
              known value.


EOF;
  exit;
}

// Password list to be processed.
$passwords = $_SERVER['argv'];

require_once __DIR__ . '/../vendor/autoload.php';

$core_services = CoreServices::create()->disableContainerDumping();

$password_hasher = $core_services->Container->get('password');

foreach ($passwords as $password) {
  print("\npassword: $password \t\thash: ". $password_hasher->hash($password) ."\n");
}
print("\n");

