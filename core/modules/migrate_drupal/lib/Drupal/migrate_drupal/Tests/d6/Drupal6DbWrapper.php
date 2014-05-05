<?php

/**
 * @file
 * Contains \Drupal\migrate_drupal\Tests\Dump\Drupal6DumpBase.
 */

namespace Drupal\migrate_drupal\Tests\d6;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\SchemaObjectExistsException;

/**
 * Wrapper for a (temporary) Drupal 6 database.
 */
class Drupal6DbWrapper {

  /**
   * The database connection for the Drupal 6 database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Sample database schema and values.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection for the Drupal 6 database.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Returns the Drupal 6 database connection.
   *
   * @return \Drupal\Core\Database\Connection
   *   The database connection for the Drupal 6 database.
   */
  public function getConnection() {
    return $this->connection;
  }

  /**
   * Creates a new table from a Drupal table definition if it doesn't exist.
   *
   * @param string $name
   *   The name of the table to create.
   * @param array|null $table
   *   A Schema API table definition array, or
   *   NULL, for a table with a known schema.
   *
   * @return bool
   *   TRUE, if the table was created or it already exists.
   */
  public function createTable($name, $table = NULL) {
    if ($this->connection->schema()->tableExists($name)) {
      // The table already exists.
      return TRUE;
    }
    if (NULL === $table) {
      $table = $this->getTableDefinition($name);
    }
    try {
      $this->connection->schema()->createTable($name, $table);
      return TRUE;
    }
    catch (SchemaObjectExistsException $e) {
      // The table was just created in another process.
      return TRUE;
    }
  }

  /**
   * @param string $name
   *   The name of the table to create. This must be one of the "known" table
   *   names, see tableDefinitions() below.
   *
   * @throws \Exception
   * @return array
   */
  protected function getTableDefinition($name) {
    $knownDefinitions = $this->tableDefinitions();
    if (!isset($knownDefinitions[$name])) {
      throw new \Exception("Unknown table name '$name'.");
    }
    return $knownDefinitions[$name];
  }

  /**
   * Table definitions.
   *
   * @return array[]
   */
  protected function tableDefinitions() {
    return array(
      'node_type' => array(
        'description' => 'Stores information about all defined {node} types.',
        'fields' => array(
          'type' => array(
            'description' => 'The machine-readable name of this type.',
            'type' => 'varchar',
            'length' => 32,
            'not null' => TRUE),
          'name' => array(
            'description' => 'The human-readable name of this type.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'module' => array(
            'description' => 'The base string used to construct callbacks corresponding to this node type.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE),
          'description'    => array(
            'description' => 'A brief description of this type.',
            'type' => 'text',
            'not null' => TRUE,
            'size' => 'medium'),
          'help' => array(
            'description' => 'Help information shown to the user when creating a {node} of this type.',
            'type' => 'text',
            'not null' => TRUE,
            'size' => 'medium'),
          'has_title' => array(
            'description' => 'Boolean indicating whether this type uses the {node}.title field.',
            'type' => 'int',
            'unsigned' => TRUE,
            'not null' => TRUE,
            'size' => 'tiny'),
          'title_label' => array(
            'description' => 'The label displayed for the title field on the edit form.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'has_body' => array(
            'description' => 'Boolean indicating whether this type uses the {node_revisions}.body field.',
            'type' => 'int',
            'unsigned' => TRUE,
            'not null' => TRUE,
            'size' => 'tiny'),
          'body_label' => array(
            'description' => 'The label displayed for the body field on the edit form.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'min_word_count' => array(
            'description' => 'The minimum number of words the body must contain.',
            'type' => 'int',
            'unsigned' => TRUE,
            'not null' => TRUE,
            'size' => 'small'),
          'custom' => array(
            'description' => 'A boolean indicating whether this type is defined by a module (FALSE) or by a user via a module like the Content Construction Kit (TRUE).',
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0,
            'size' => 'tiny'),
          'modified' => array(
            'description' => 'A boolean indicating whether this type has been modified by an administrator; currently not used in any way.',
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0,
            'size' => 'tiny'),
          'locked' => array(
            'description' => 'A boolean indicating whether the administrator can change the machine name of this type.',
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0,
            'size' => 'tiny'),
          'orig_type' => array(
            'description' => 'The original machine-readable name of this node type. This may be different from the current type name if the locked field is 0.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => '',
          ),
        ),
        'primary key' => array('type'),
      ),
      'variable' => array(
        'fields' => array(
          'name' => array(
            'type' => 'varchar',
            'length' => 128,
            'not null' => TRUE,
            'default' => '',
          ),
          'value' => array(
            'type' => 'blob',
            'not null' => TRUE,
            'size' => 'big',
            'translatable' => TRUE,
          ),
        ),
        'primary key' => array(
          'name',
        ),
        'module' => 'book',
        'name' => 'variable',
      ),
      'system' => array(
        'description' => "A list of all modules, themes, and theme engines that are or have been installed in Drupal's file system.",
        'fields' => array(
          'filename' => array(
            'description' => 'The path of the primary file for this item, relative to the Drupal root; e.g. modules/node/node.module.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'name' => array(
            'description' => 'The name of the item; e.g. node.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'type' => array(
            'description' => 'The type of the item, either module, theme, or theme_engine.',
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'owner' => array(
            'description' => "A theme's 'parent'. Can be either a theme or an engine.",
            'type' => 'varchar',
            'length' => 255,
            'not null' => TRUE,
            'default' => ''),
          'status' => array(
            'description' => 'Boolean indicating whether or not this item is enabled.',
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0),
          'throttle' => array(
            'description' => 'Boolean indicating whether this item is disabled when the throttle.module disables throttleable items.',
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0,
            'size' => 'tiny'),
          'bootstrap' => array(
            'description' => "Boolean indicating whether this module is loaded during Drupal's early bootstrapping phase (e.g. even before the page cache is consulted).",
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0),
          'schema_version' => array(
            'description' => "The module's database schema version number. -1 if the module is not installed (its tables do not exist); 0 or the largest N of the module's hook_update_N() function that has either been run
   or existed when the module was first installed.",
            'type' => 'int',
            'not null' => TRUE,
            'default' => -1,
            'size' => 'small'),
          'weight' => array(
            'description' => "The order in which this module's hooks should be invoked relative to other modules. Equal-weighted modules are ordered by name.",
            'type' => 'int',
            'not null' => TRUE,
            'default' => 0),
          'info' => array(
            'description' => "A serialized array containing information from the module's .info file; keys can include name, description, package, version, core, dependencies, dependents, and php.",
            'type' => 'text',
            'not null' => FALSE,
          )),
        'primary key' => array('filename'),
        'indexes' => array(
          'modules' => array(array('type', 12), 'status', 'weight', 'filename'),
          'bootstrap' => array(array('type', 12), 'status', 'bootstrap', 'weight', 'filename'),
          'type_name' => array(array('type', 12), 'name'),
        ),
      ),
    );
  }

  /**
   * Sets a module version and status.
   *
   * @param string $module
   * @param string $version
   * @param int $status
   */
  public function setModuleVersion($module, $version, $status = 1) {
    $this->createTable('system');
    $this->connection->merge('system')
      ->key(array('filename' => "modules/$module"))
      ->fields(array(
        'type' => 'module',
        'name' => $module,
        'schema_version' => $version,
        'status' => $status,
      ))
      ->execute();
  }
}
