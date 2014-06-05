<?php

namespace Drupal\simpletest;

use Drupal\Core\Config\StorageInterface;

class ConfigMemoryStorage implements StorageInterface {

  /**
   * The module list that will be returned to the module handler.
   *
   * @var array
   */
  protected $moduleList;

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
  }

  /**
   * {@inheritdoc}
   */
  public function read($name) {
    if ($name == 'system.module') {
      return $this->moduleList;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names)
  {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data) {
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name) {
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
  }

  /**
   * Sets the module list that will be returned to the module handler.
   *
   * @param $module_list
   */
  public function setModuleList($module_list) {
    $this->moduleList = $module_list;
  }

}
