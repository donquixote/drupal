<?php

namespace Drupal\Core\Extension\SearchdirPrefixes;

use Drupal\Core\Extension\SitePath\SitePath_Static;
use Drupal\Core\Extension\SitePath\SitePathInterface;

class SearchdirPrefixes_Common extends SearchdirPrefixesBase {

  /**
   * @var \Drupal\Core\Extension\SitePath\SitePathInterface
   */
  private $sitePathProvider;

  /**
   * @param string $sitePath
   *
   * @return \Drupal\Core\Extension\SearchdirPrefixes\SearchdirPrefixes_Common
   */
  public static function createWithStaticSitePath($sitePath) {
    $sitePathProvider = new SitePath_Static($sitePath);
    return new self($sitePathProvider);
  }

  /**
   * @param \Drupal\Core\Extension\SitePath\SitePathInterface $sitePathProvider
   */
  public function __construct(SitePathInterface $sitePathProvider) {
    $this->sitePathProvider = $sitePathProvider;
  }

  /**
   * @return string[]
   *   Format: $[$searchdir_prefix] = $searchdir_weight
   *   E.g. ['core/' => 0, '' => 1, 'sites/default' => 2]
   */
  public function getSearchdirPrefixWeights() {

    $prefix_weights = parent::getSearchdirPrefixWeights();

    if (NULL !== $site_path = $this->sitePathProvider->getSitePath()) {
      $prefix_weights[$site_path . '/'] = static::ORIGIN_SITE;
    }

    return $prefix_weights;
  }
}
