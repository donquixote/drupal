<?php

use Drupal\Component\Autoload\ClassLoaderInterface;
use Drupal\Component\Autoload\ClassLoader;

$drupal_root = dirname(__DIR__);

// Include the Drupal ClassLoader for loading PSR-0-compatible classes.
require_once $drupal_root . '/core/lib/Drupal/Core/ClassLoader/ClassLoaderInterface.php';
require_once $drupal_root . '/core/lib/Drupal/Core/ClassLoader/AbstractClassLoader.php';
require_once $drupal_root . '/core/lib/Drupal/Core/ClassLoader/ClassLoader.php';
$loader = new ClassLoader();