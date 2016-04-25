<?php

namespace Drupal\Core\Extension\InfoList;

interface ExtensionInfoListInterface {

  /**
   * Resets cached or buffered extension info.
   *
   * @return $this
   */
  public function reset();

  /**
   * Returns an array of information about enabled modules or themes.
   *
   * This function returns the contents of the .info.yml file for each installed
   * extension.
   *
   * @return array[]
   *   An associative array of extension information keyed by name. If no
   *   records are available, an empty array is returned.
   */
  public function getAllInfo();

}
