<?php

namespace Drupal\Core\Extension\List_;

class ExtensionList_MultipleProcessorDecorator implements ExtensionListingInterface {

  /**
   * @var \Drupal\Core\Extension\List_\ExtensionListingInterface
   */
  private $decorated;

  /**
   * @var \Drupal\Core\Extension\List_\Processor\ExtensionListProcessorInterface[]
   */
  private $processors;

  /**
   * @param \Drupal\Core\Extension\List_\ExtensionListingInterface $decorated
   * @param \Drupal\Core\Extension\List_\Processor\ExtensionListProcessorInterface[] $processors
   */
  public function __construct(ExtensionListingInterface $decorated, array $processors) {
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
  public function listExtensions() {
    $extensions = $this->decorated->listExtensions();
    foreach ($this->processors as $processor) {
      $processor->processExtensions($extensions);
    }
    return $extensions;
  }
}
