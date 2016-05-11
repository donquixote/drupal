<?php

namespace Drupal\Core\Extension\ExtensionsByName;

interface ExtensionsByNameInterface {

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset();

  /**
   * Returns all available extensions, with $extension->info filled in.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   Format: $[$name] = $extension
   */
  public function getExtensions();

}
