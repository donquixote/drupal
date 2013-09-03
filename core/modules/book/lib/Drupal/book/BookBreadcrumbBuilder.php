<?php

/**
 * @file
 * Contains Drupal\book\BookBreadcrumbBuilder.
 */

namespace Drupal\book;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\Routing\RouteCompiler;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Access\AccessManager;
use Symfony\Cmf\Component\Routing\DynamicRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Route;

/**
 * Provides a breadcrumb builder for nodes in a book.
 */
class BookBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  /**
   * The menu link storage controller.
   *
   * @var \Drupal\menu_link\MenuLinkStorageControllerInterface
   */
  protected $menuLinkStorage;

  /**
   * The menu link access service.
   *
   * @var \Drupal\Core\Access\AccessManager
   */
  protected $accessManager;

  /**
   * The translation manager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface;
   */
  protected $translation;


  /**
   * Constructs the MenuLinkBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider service.
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Access\AccessManager $access_manager
   *   The menu link access service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The translation manager service.
   * @param \Symfony\Cmf\Component\Routing\DynamicRouter $dynamic_router
   *   The dynamic router service.
   */
  public function __construct(EntityManager $entity_manager, AccessManager $access_manager, TranslationInterface $translation) {
    $this->menuLinkStorage = $entity_manager->getStorageController('menu_link');
    $this->accessManager = $access_manager;
    $this->translation = $translation;
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $attributes) {
    // @todo - like \Drupal\forum\ForumBreadcrumbBuilder this depends on the
    // legacy non-route node view. It must be updated once that's converted.
    if (!empty($attributes['_drupal_menu_item']) && !empty($attributes['_drupal_menu_item']['map'][1]->book)) {
      $mlids = array();
      // @todo Replace with link generator service when
      //   https://drupal.org/node/2047619 lands.
      $links = array(l($this->translation->translate('Home'), '<front>'));
      $book = $attributes['_drupal_menu_item']['map'][1]->book;
      $depth = 1;
      // We skip the current node.
      while (!empty($book['p' . ($depth + 1)])) {
        $mlids[] = $book['p' . $depth];
        $depth++;
      }
      $menu_links = $this->menuLinkStorage->loadMultiple($mlids);
      if (count($menu_links) > 0) {
        $depth = 1;
        while (!empty($book['p' . ($depth + 1)])) {
          if (!empty($menu_links[$book['p' . $depth]]) && ($menu_link = $menu_links[$book['p' . $depth]])) {
            // Legacy hook_menu page callback.
            // @todo change this once thie node view route is converted.
            if ($item = menu_get_item($menu_link->link_path)) {
              if ($item['access']) {
                $links[] = l($menu_link->label(), $menu_link->link_path, $menu_link->options);
              }
            }
          }
          $depth++;
        }
      }
      return $links;
    }
  }

}
