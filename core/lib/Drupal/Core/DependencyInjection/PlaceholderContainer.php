<?php


namespace Drupal\Core\DependencyInjection;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ScopeInterface;

/**
 * A placeholder container that throws an exception whenever it does anything.
 *
 * Used in
 * @see \Drupal
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
}
