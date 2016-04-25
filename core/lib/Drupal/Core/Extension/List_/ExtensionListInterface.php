<?php

namespace Drupal\Core\Extension\List_;

interface ExtensionListInterface {

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
   */
  public function listExtensions();

}
