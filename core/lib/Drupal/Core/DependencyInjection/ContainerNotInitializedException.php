<?php


namespace Drupal\Core\DependencyInjection;

/**
 * Exception thrown when a method is called that requires a container, but the
 * container is not initialized yet.
 *
 * @see \Drupal
 * @see \Drupal\Core\DependencyInjection\PlaceholderContainer
 */
class ContainerNotInitializedException extends \Exception {

}
