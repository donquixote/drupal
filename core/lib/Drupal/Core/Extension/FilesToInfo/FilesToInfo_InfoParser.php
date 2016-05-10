<?php

namespace Drupal\Core\Extension\FilesToInfo;

use Drupal\Core\Extension\InfoParser;
use Drupal\Core\Extension\InfoParserInterface;

class FilesToInfo_InfoParser implements FilesToInfoInterface {

  /**
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  private $infoParser;

  /**
   * @return \Drupal\Core\Extension\FileToInfo\FileToInfoInterface
   */
  static function create() {
    return new self(new InfoParser());
  }

  /**
   * @param \Drupal\Core\Extension\InfoParserInterface $infoParser
   */
  function __construct(InfoParserInterface $infoParser) {
    $this->infoParser = $infoParser;
  }

  /**
   * @param string[] $files
   *   Format: $[] = 'core/modules/system/system.info.yml'
   *
   * @return array[]
   *   Format: $['core/modules/system/system.info.yml'] = $info
   */
  public function filesGetInfoArrays(array $files) {
    $info_by_file = [];
    foreach ($files as $file) {
      $info_by_file[$file] = $this->infoParser->parse($file);
    }
    return $info_by_file;
  }
}
