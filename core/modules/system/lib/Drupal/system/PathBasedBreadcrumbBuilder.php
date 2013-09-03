<?php

/**
 * @file
 * Contains \Drupal\system\PathBasedBreadcrumbBuilder.
 */

namespace Drupal\system;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Access\AccessManager;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Component\Utility\Unicode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class to define the menu_link breadcrumb builder.
 */
class PathBasedBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

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
   * The menu storage controller.
   *
   * @var \Drupal\Core\Config\Entity\ConfigStorageController
   */
  protected $menuStorage;

  /**
   * The dynamic router service.
   *
   * @var \Symfony\Component\Routing\Matcher\RequestMatcherInterface
   */
  protected $router;

  /**
   * The dynamic router service.
   *
   * @var \Drupal\Core\PathProcessor\InboundPathProcessorInterface
   */
  protected $pathProcessor;

  /**
   * Site config object.
   *
   * @var \Drupal\Core\Config\Config
   */


  /**
   * Constructs the MenuLinkBreadcrumbBuilder.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current Request object.
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Access\AccessManager $access_manager
   *   The menu link access service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The translation manager service.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $path_processor
   *   The inbound path processor.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory service.
   */
  public function __construct(Request $request, EntityManager $entity_manager, AccessManager $access_manager, TranslationInterface $translation, RequestMatcherInterface $router, InboundPathProcessorInterface $path_processor, ConfigFactory $config_factory) {
    $this->request = $request;
    $this->accessManager = $access_manager;
    $this->translation = $translation;
    $this->menuStorage = $entity_manager->getStorageController('menu');
    $this->router = $router;
    $this->pathProcessor = $path_processor;
    $this->config = $config_factory->get('system.site');
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $attributes) {
    $links = array();

    // Custom breadcrumb behaviour for editing menu links, we append a link to
    // the menu in which the link is found.
    if (!empty($attributes['_route']) && $attributes['_route'] == 'menu_link_edit' && !empty($attributes['menu_link'])) {
      $menu_link = $attributes['menu_link'];
      if (!$menu_link->isNew()) {
        // Add a link to the menu admin screen.
        $menu = $this->menuStorage->load($menu_link->menu_name);
        $links[] = l($menu->label(), 'admin/structure/menu/manage/' . $menu_link->menu_name);
      }
    }

    // General path-based breadcrumbs. Use the original, aliased path.
    $path = trim($this->request->getPathInfo(), '/');
    $path_elements = explode('/', $path);
    $front = $this->config->get('page.front');
    while (count($path_elements) > 1) {
      array_pop($path_elements);
      // Copy the path elements for up-casting.
      $pattern = implode('/', $path_elements);
      if ($pattern == $front) {
        // Don't show a link to the front-page path.
        continue;
      }
      if ($pattern == 'user') {
        // /user is just a redirect, so skip it.
        continue;
      }
      $route_request = $this->getRequestForPath($pattern);
      if ($route_request) {
        $access = FALSE;
        // @todo - remove this once all of core is converted to the new router.
        if ($route_request->attributes->get('_legacy')) {
          $menu_item = $route_request->attributes->get('_drupal_menu_item');
          if (($menu_item['type'] & MENU_LINKS_TO_PARENT) == MENU_LINKS_TO_PARENT) {
            continue;
          }
          $access = $menu_item['access'];
          $title = $menu_item['title'];
        }
        else {
          $route_name = $route_request->attributes->get(RouteObjectInterface::ROUTE_NAME);
          // Note that the parameters don't really matter here since we're
          // passing in the request which already has the upcast attributes.
          $parameters = array();
          $access = $this->accessManager->checkNamedRoute($route_name, $parameters, $route_request);
          $title = $route_request->attributes->get('_title');
        }
        if ($access) {
          if (!$title) {
            // @todo Revisit when
            $title = str_replace(array('-', '_'), ' ', Unicode::ucfirst(end($path_elements)));
          }
          // @todo Replace with a #type => link render element when
          //   https://drupal.org/node/2047619 lands.
          $links[] = l($title, $route_request->attributes->get('_system_path'));
        }
      }

    }
    // @todo Replace with a #type => link render element when
    //   https://drupal.org/node/2047619 lands.
    if ($path && $path != $front) {
      // Add the Home link, except for the front page.
      $links[] = l($this->translation->translate('Home'), '<front>');
    }
    return array_reverse($links);
  }

  /**
   * Matches a path in the router.
   *
   * @param string $path
   *   The request path.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   A populated request object or NULL if the patch couldn't be matched.
   */
  protected function getRequestForPath($path) {
    $request = Request::create($path);
    $processed = $this->pathProcessor->processInbound($path, $request);
    $request->attributes->set('_system_path', $processed);
    // Attempt to match this path to provide a fully built request.
    try {
      $request->attributes->add($this->router->matchRequest($request));
      return $request;
    }
    catch (NotFoundHttpException $e) {
      return NULL;
    }
    catch (ResourceNotFoundException $e) {
      return NULL;
    }
  }

}
