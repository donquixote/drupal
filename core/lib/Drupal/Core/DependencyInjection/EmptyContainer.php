<?php

/**
 * @file
 * Contains \Drupal\Core\DependencyInjection\Container.
 */

namespace Drupal\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\Container as SymfonyContainer;

/**
 * Extends the symfony container to set the service ID on the created object.
 */
class EmptyContainer extends SymfonyContainer {

  /**
   * {@inheritdoc}
   */
  public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) {
    throw new \Exception('The service container is not initialized yet.');
  }

}
