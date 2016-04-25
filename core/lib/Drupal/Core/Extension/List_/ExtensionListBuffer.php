<?php

namespace Drupal\Core\Extension\List_;

class ExtensionListBuffer implements ExtensionListInterface {

  /**
   * @var \Drupal\Core\Extension\List_\ExtensionListInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\Extension[]|null
   */
  private $extensions;

  /**
   * @param \Drupal\Core\Extension\List_\ExtensionListInterface $decorated
   */
  function __construct(ExtensionListInterface $decorated) {
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
  public function listExtensions() {
    return NULL !== $this->extensions
      ? $this->extensions
      : $this->extensions = $this->decorated->listExtensions();
  }
}
