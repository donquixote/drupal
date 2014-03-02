<?php


namespace Drupal\Core\Site;


class SiteDirectory {

  /**
   * The absolute path to the Drupal root directory.
   *
   * @var string
   */
  private $root;

  /**
   * @var string
   */
  private $path;

  /**
   * @param string $root
   * @param string $path
   *
   * @throws \InvalidArgumentException
   */
  function __construct($root, $path) {
    $this->root = $root;
    // Extra safety protection in case a script somehow manages to bypass all
    // other protections.
    if (!is_string($path)) {
      throw new \InvalidArgumentException('Invalid site path.');
    }
    $this->path = $path;
  }

  /**
   * Prefixes a given filepath with the site directory, if any.
   *
   * Site::getPath() and this helper method only exists to ensure that a given
   * filepath does not result in an absolute filesystem path in case of a string
   * concatenation like the following:
   *
   * @code
   * // If the sire directory path is empty (root directory), then the resulting
   * // filesystem path would become absolute; i.e.: "/some/file"
   * unlink($site_path . '/some/file');
   * @endcode
   *
   * In case the PHP process has write access to the entire filesystem, such a
   * file operation could succeed and potentially affect arbitrary other files
   * and directories that happen to exist. That must not happen.
   *
   * @param string $filepath
   *   The filepath to prefix.
   *
   * @throws \RuntimeException
   * @return string
   *   The prefixed filepath.
   */
  public function resolvePath($filepath) {
    // A faulty call to Site::getPath() might include a leading slash (/), in
    // which case the entire site path resolution of this function would be
    // pointless, because the resulting path would still be absolute. Therefore,
    // guarantee that even a bogus argument is resolved correctly.
    $filepath = ltrim($filepath, '/');

    if ($this->path !== '') {
      if ($filepath !== '') {
        return $this->path . '/' . $filepath;
      }
      return $this->path;
    }
    return $filepath;
  }

  /**
   * Returns a given path as absolute path in the site directory.
   *
   * @param string $filepath
   *   (optional) A relative filepath to append to the site path.
   *
   * @return string
   *   The given $filepath, potentially prefixed with the site path, as an
   *   absolute filesystem path.
   */
  public function getAbsolutePath($filepath = '') {
    $filepath = $this->resolvePath($filepath);
    if ($filepath !== '') {
      return $this->root . '/' . $filepath;
    }
    else {
      return $this->root;
    }
  }

} 
