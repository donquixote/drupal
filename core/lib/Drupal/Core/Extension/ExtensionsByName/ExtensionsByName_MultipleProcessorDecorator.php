<?php

namespace Drupal\Core\Extension\ExtensionsByName;

class ExtensionsByName_MultipleProcessorDecorator implements ExtensionsByNameInterface {

  /**
   * @var \Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessorInterface[]
   */
  private $processors;

  /**
   * @param \Drupal\Core\Extension\ExtensionsByName\ExtensionsByNameInterface $decorated
   * @param \Drupal\Core\Extension\ExtensionsProcessor\ExtensionsProcessorInterface[] $processors
   */
  public function __construct(ExtensionsByNameInterface $decorated, array $processors) {
    $this->decorated = $decorated;
    $this->processors = $processors;
  }

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset() {
    $this->decorated->reset();
    return $this;
  }

  /**
   * Returns all available extensions, with $extension->info filled in.
   *
   * @return \Drupal\Core\Extension\Extension[]
   */
  public function getExtensions() {
    $extensions = $this->decorated->getExtensions();
    foreach ($this->processors as $processor) {
      $processor->processExtensions($extensions);
    }
    return $extensions;
  }
}
