<?php

namespace Drupal\Core\Extension\List_\Raw;

interface RawExtensionListInterface {

  /**
   * Resets any stored or cached extension list.
   *
   * @return $this
   */
  public function reset();

  /**
   * Returns all available extensions, with $extension->info possibly NOT yet
   * filled in.
   *
   * It can happen that other components further modify these objects, and add
   * the ->info array and more.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   Format: $[$extension_name] = $extension
   *   E.g. $['system'] = new Extension(..)
   */
  public function getRawExtensions();

}
