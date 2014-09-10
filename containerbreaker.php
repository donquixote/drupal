<?php

require_once __DIR__ . '/core/vendor/autoload.php';

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\YamlFileLoader;

$container = new ContainerBuilder();
$loader = new YamlFileLoader($container);
$loader->load('core/core.services.yml');

var_export($container->get('info_parser'));

# $container->compile();

echo 'All done.';
