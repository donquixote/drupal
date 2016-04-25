<?php

namespace Drupal\Tests\Core\Extension;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;
use Drupal\Core\Extension\Hub\ExtensionHub;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\List_\DiscoveryExtensionListBase;
use Drupal\Tests\UnitTestCase;
use org\bovigo\vfs\vfsStream;
use Prophecy\Argument;

/**
 * @coversDefaultClass \Drupal\Core\Extension\Hub\ExtensionHub
 * @group Extension
 */
class ExtensionHubTest extends UnitTestCase {

  /**
   * @covers ::nameGetLabel
   * @expectedException \InvalidArgumentException
   */
  public function testGetNameWithNonExistingExtension() {
    $test_extension_hub = $this->setupEmptyTestExtensionHub();

    $test_extension_hub->nameGetLabel('test_name');
  }

  /**
   * @covers ::nameGetLabel
   */
  public function testGetName() {
    $test_extension_hub = $this->setupTestExtensionHub();

    static::assertEquals('test name', $test_extension_hub->nameGetLabel('test_name'));
  }

  /**
   * @covers ::nameGetExtension
   * @expectedException \InvalidArgumentException
   */
  public function testGetExtensionWithNonExistingExtension() {
    $test_extension_hub = $this->setupEmptyTestExtensionHub();

    $test_extension_hub->nameGetExtension('test_name');
  }

  /**
   * @covers ::nameGetExtension
   */
  public function testGetExtension() {
    $test_extension_hub = $this->setupTestExtensionHub();

    $extension = $test_extension_hub->nameGetExtension('test_name');
    static::assertInstanceOf(Extension::class, $extension);
    static::assertEquals('test_name', $extension->getName());
  }

  /**
   * @covers ::listExtensions
   */
  public function testListExtensions() {
    $test_extension_list = $this->setupTestExtensionHub();

    $extensions = $test_extension_list->listExtensions();
    static::assertCount(1, $extensions);
    static::assertEquals('test_name', $extensions['test_name']->getName());
  }

  /**
   * @covers ::nameGetInfo
   * @covers ::getAllInfo
   */
  public function testGetInfo() {
    $test_extension_list = $this->setupTestExtensionHub();

    $info = $test_extension_list->nameGetInfo('test_name');
    static::assertEquals([
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
    $test_extension_list = $this->setupTestExtensionHub();

    $infos = $test_extension_list->getAllInfo();
    static::assertEquals(['test_name' => [
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
    $test_extension_list = $this->setupTestExtensionHub();

    $filenames = $test_extension_list->getFilenames();
    static::assertEquals([
      'test_name' => 'vfs://drupal_root/example/test_name/test_name.info.yml',
    ], $filenames);
  }

  /**
   * @covers ::nameGetFilename
   */
  public function testGetFilename() {
    $test_extension_list = $this->setupTestExtensionHub();

    $filename = $test_extension_list->nameGetFilename('test_name');
    static::assertEquals('vfs://drupal_root/example/test_name/test_name.info.yml', $filename);
  }


  /**
   * @covers ::nameSetFilename
   * @covers ::nameGetFilename
   */
  public function testSetFilename() {
    $test_extension_list = $this->setupTestExtensionHub();

    $test_extension_list->nameSetFilename('test_name', 'vfs://drupal_root/example2/test_name/test_name.info.yml');
    static::assertEquals('vfs://drupal_root/example2/test_name/test_name.info.yml', $test_extension_list->nameGetFilename('test_name'));
  }

  /**
   * @covers ::nameGetPath
   */
  public function testGetPath() {
    $test_extension_list = $this->setupTestExtensionHub();

    $path = $test_extension_list->nameGetPath('test_name');
    static::assertEquals('vfs://drupal_root/example/test_name', $path);
  }

  /**
   * @return \Drupal\Core\Extension\Hub\ExtensionHubInterface
   */
  protected function setupEmptyTestExtensionHub() {
    list($cache, $info_parser, $module_handler) = $this->getMocks();

    $extension_discovery = $this->prophesize(ExtensionDiscovery::class);
    $extension_discovery->scan('test_extension')->willReturn([]);

    return ExtensionHub::create(
      'test_extension',
      TestExtensionList::create(
        'test_extension',
        $info_parser->reveal(),
        $module_handler->reveal(),
        $extension_discovery->reveal()),
      $cache->reveal());
  }

  /**
   * @return \Drupal\Core\Extension\Hub\ExtensionHubInterface
   */
  protected function setupTestExtensionHub() {
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

    $extension_discovery = $this->prophesize(ExtensionDiscovery::class);
    $extension_discovery->scan('test_extension')->willReturn(['test_name' => new Extension($this->root, 'test_extension', 'vfs://drupal_root/example/test_name/test_name.info.yml')]);

    return ExtensionHub::create(
      'test_extension',
      TestExtensionList::create(
        'test_extension',
        $info_parser->reveal(),
        $module_handler->reveal(),
        $extension_discovery->reveal()),
      $cache->reveal());
  }

  protected function getMocks() {
    $cache = $this->prophesize(CacheBackendInterface::class);
    $info_parser = $this->prophesize(InfoParserInterface::class);
    $module_handler = $this->prophesize(ModuleHandlerInterface::class);
    return [$cache, $info_parser, $module_handler];
  }

}

class TestExtensionList extends DiscoveryExtensionListBase {

  /**
   * @param string $type
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Extension\ExtensionDiscovery $extension_discovery
   *
   * @return \Drupal\Core\Extension\List_\ExtensionListInterface
   */
  static function create(
    $type,
    InfoParserInterface $info_parser,
    ModuleHandlerInterface $module_handler,
    ExtensionDiscovery $extension_discovery
  ) {
    return new self(
      $type,
      $info_parser,
      $module_handler,
      $extension_discovery,
      []);
  }

}
