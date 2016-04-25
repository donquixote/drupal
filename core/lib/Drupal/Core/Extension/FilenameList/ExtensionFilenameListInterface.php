<?php

namespace Drupal\Core\Extension\FilenameList;

interface ExtensionFilenameListInterface {

  /**
   * Resets cached or buffered extension file names.
   *
   * @return $this
   */
  public function reset();

  /**
   * Returns a list of extension folder names keyed by extension name.
   *
   * @return string[]
   */
  public function getFilenames();

}
