<?php

namespace Drupal\Core\Extension\ExtensionsByName;

class ExtensionsByName_Buffer implements ExtensionsByNameInterface {

  /**
   * @var \Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\Extension[]|null
   */
  private $extensions;

  /**
   * @param \Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameInterface $decorated
   */
  function __construct(ExtensionsByNameInterface $decorated) {
    $this->decorated = $decorated;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->decorated->reset();
    $this->extensions = NULL;
    return $this;
  }

  /**
   * Returns all available extensions, with $extension->info filled in.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getExtensions() {
    return NULL !== $this->extensions
      ? $this->extensions
      : $this->extensions = $this->decorated->getExtensions();
  }
}
