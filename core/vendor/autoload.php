<?php

/**
 * @file
 * Drupal-specific autoload.php to be copied to replace core/vendor/autoload.php
 * generated by Composer.
 *
 * Having a Drupal-specific autoload.php makes it easier to use a ClassLoader
 * implementation different from the one provided by Composer.
 *
 * The file will be copied to replace core/vendor/autoload.php, by a script
 * triggered after composer autoload-dump. That script is in
 * Drupal\Core\Autoload\GeneratorScript::postAutoloadDump().
 *
 * This means, the official autoload.php to include in scripts will always be
 * core/vendor/autoload.php.
 */

use Drupal\Core\Autoload\DrupalAutoloaderInit;

require_once __DIR__ . '/lib/Drupal/Core/Autoload/DrupalAutoloaderInit.php';

return DrupalAutoloaderInit::getLoader();
