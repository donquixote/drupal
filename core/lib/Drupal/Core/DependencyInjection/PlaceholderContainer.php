<?php


namespace Drupal\Core\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;

/**
 * A placeholder container that throws an exception whenever it does anything.
 *
 * Used in
 * @see \Drupal::$container
 * @see \Drupal\Core\DrupalKernel::$container
 */
class PlaceholderContainer implements ContainerInterface {

  /**
   * Message for ContainerNotInitializedException.
   * 
   * @var string
   */
  protected $message;

  /**
   * Constructs a PlaceholderContainer object.
   * 
   * @param string $exception_message
   *   Message for ContainerNotInitializedException.
   */
  public function __construct($exception_message = NULL) {
    $this->message = isset($exception_message)
      ? $exception_message
      : 'Container not initialized.';
  }

  /**
   * {@inheritdoc}
   */
  public function set($id, $service, $scope = self::SCOPE_CONTAINER) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function has($id) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getParameter($name) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function hasParameter($name) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function setParameter($name, $value) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function enterScope($name) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function leaveScope($name) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function addScope(ScopeInterface $scope) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function hasScope($name) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * {@inheritdoc}
   */
  public function isScopeActive($name) {
    throw new ContainerNotInitializedException($this->message);
  }

  /**
   * Checks whether the given service has been initialized yet.
   *
   * This method is not part of Symfony's ContainerInterface, but it is needed
   * for DrupalKernel::$container.
   *
   * @param string $id
   *   The service identifier
   *
   * @return bool
   *   TRUE, if the service has already been initialized. FALSE, otherwise
   * @throws \Drupal\Core\DependencyInjection\ContainerNotInitializedException
   *
   * @see \Drupal\Core\DependencyInjection\Container::initialized()
   * @see \Drupal\Core\DrupalKernel::$container
   */
  public function initialized($id) {
    throw new ContainerNotInitializedException($this->message);
  }

}
