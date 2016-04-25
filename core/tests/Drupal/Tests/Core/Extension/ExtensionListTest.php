<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\Core\Extension\ExtensionList
 * @group Extension
 */
class ExtensionListTest extends UnitTestCase {

  /**
   * @covers ::getName
   * @expectedException \InvalidArgumentException
   */
  public function testGetNameWithNonExistingExtension() {
    list($cache, $info_parser, $module_handler) = $this->getMocks();
    $test_extension_list = new TestExtension($this->root, 'test_extension', $cache->reveal(), $info_parser->reveal(), $module_handler->reveal());

    $extension_discovery = $this->prophesize(ExtensionDiscovery::class);
    $extension_discovery->scan('test_extension')->willReturn([]);
    $test_extension_list->setExtensionDiscovery($extension_discovery->reveal());

    $test_extension_list->nameGetLabel('test_name');
  }

  /**
   * @covers ::getName
   */
  public function testGetName() {
    $test_extension_list = $this->setupTestExtensionList();

    $this->assertEquals('test name', $test_extension_list->nameGetLabel('test_name'));
  }

  /**
   * @covers ::getExtension
   * @expectedException \InvalidArgumentException
   */
  public function testGetExtensionWithNonExistingExtension() {
    list($cache, $info_parser, $module_handler) = $this->getMocks();
    $test_extension_list = new TestExtension($this->root, 'test_extension', $cache->reveal(), $info_parser->reveal(), $module_handler->reveal());

    $extension_discovery = $this->prophesize(ExtensionDiscovery::class);
    $extension_discovery->scan('test_extension')->willReturn([]);
    $test_extension_list->setExtensionDiscovery($extension_discovery->reveal());

    $test_extension_list->nameGetExtension('test_name');
  }

  /**
   * @covers ::getExtension
   */
  public function testGetExtension() {
    $test_extension_list = $this->setupTestExtensionList();

    $extension = $test_extension_list->nameGetExtension('test_name');
    $this->assertInstanceOf(Extension::class, $extension);
    $this->assertEquals('test_name', $extension->getName());
  }

  /**
   * @covers ::listExtensions
   */
  public function testListExtensions() {
    $test_extension_list = $this->setupTestExtensionList();

    $extensions = $test_extension_list->listExtensions();
    $this->assertCount(1, $extensions);
    $this->assertEquals('test_name', $extensions['test_name']->getName());
  }

  /**
   * @covers ::getInfo
   * @covers ::getAllInfo
   */
  public function testGetInfo() {
    $test_extension_list = $this->setupTestExtensionList();

    $info = $test_extension_list->nameGetInfo('test_name');
    $this->assertEquals([
      'type' => 'test_extension',
      'core' => '8.x',
      'name' => 'test name',
      'mtime' => 123456789,
    ], $info);
  }

  /**
   * @covers ::getAllInfo
   */
  public function testGetAllInfo() {
    $test_extension_list = $this->setupTestExtensionList();

    $infos = $test_extension_list->getAllInfo();
    $this->assertEquals(['test_name' => [
      'type' => 'test_extension',
      'core' => '8.x',
      'name' => 'test name',
      'mtime' => 123456789,
    ]], $infos);
  }

  /**
   * @covers ::getFilenames
   */
  public function testGetFilenames() {
    $test_extension_list = $this->setupTestExtensionList();

    $filenames = $test_extension_list->getFilenames();
    $this->assertEquals([
      'test_name' => 'vfs://drupal_root/example/test_name/test_name.info.yml',
    ], $filenames);
  }

  /**
   * @covers ::getFilename
   */
  public function testGetFilename() {
    $test_extension_list = $this->setupTestExtensionList();

    $filename = $test_extension_list->nameGetFilename('test_name');
    $this->assertEquals('vfs://drupal_root/example/test_name/test_name.info.yml', $filename);
  }


  /**
   * @covers ::setFilename
   * @covers ::getFilename
   */
  public function testSetFilename() {
    $test_extension_list = $this->setupTestExtensionList();

    $test_extension_list->nameSetFilename('test_name', 'vfs://drupal_root/example2/test_name/test_name.info.yml');
    $this->assertEquals('vfs://drupal_root/example2/test_name/test_name.info.yml', $test_extension_list->nameGetFilename('test_name'));
  }

  /**
   * @covers ::getPath
   */
  public function testGetPath() {
    $test_extension_list = $this->setupTestExtensionList();

    $path = $test_extension_list->nameGetPath('test_name');
    $this->assertEquals('vfs://drupal_root/example/test_name', $path);
  }

  /**
   * @return \Drupal\Tests\Core\Extension\TestExtension
   */
  protected function setupTestExtensionList() {
    vfsStream::setup('drupal_root');
    vfsStream::create([
      'example' => [
        'test_name' => [
          'test_name.info.yml' => Yaml::encode([
            'name' => 'test name',
            'type' => 'test_extension',
            'core' => '8.x',
          ]),
        ],
      ],
    ]);
    touch('vfs://drupal_root/example/test_name/test_name.info.yml', 123456789);

    list($cache, $info_parser, $module_handler) = $this->getMocks();
    $info_parser->parse(Argument::any())->will(function($args) {
      return Yaml::decode(file_get_contents($args[0]));
    });

    $test_extension_list = new TestExtension('vfs://drupal_root', 'test_extension', $cache->reveal(), $info_parser->reveal(), $module_handler->reveal());

    $extension_discovery = $this->prophesize(ExtensionDiscovery::class);
    $extension_discovery->scan('test_extension')->willReturn(['test_name' => new Extension($this->root, 'test_extension', 'vfs://drupal_root/example/test_name/test_name.info.yml')]);
    $test_extension_list->setExtensionDiscovery($extension_discovery->reveal());
    return $test_extension_list;
  }

  protected function getMocks() {
    $cache = $this->prophesize(CacheBackendInterface::class);
    $info_parser = $this->prophesize(InfoParserInterface::class);
    $module_handler = $this->prophesize(ModuleHandlerInterface::class);
    return [$cache, $info_parser, $module_handler];
  }

}

class TestExtension extends ExtensionList {

  public function setExtensionDiscovery(ExtensionDiscovery $extension_discovery) {
    $this->extensionDiscovery = $extension_discovery;
  }

}
