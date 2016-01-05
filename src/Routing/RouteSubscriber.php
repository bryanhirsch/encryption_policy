<?php

/**
 * @file
 * Contains \Drupal\encryption_policy\Routing\RouteSubscriber.
 */

namespace Drupal\encryption_policy\Routing;

use Drupal\Core\Routing\RouteProvider;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\encryption_policy\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  private $routeProvider;

  public function __construct(RouteProvider $route_provider) {
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // Check if some other module (e.g. password_policy) has already provided /admin/config/security.
    if ($this->pathExists('/admin/config/security')) {
      // Our work is done.
      return;
    }

    // Provide security "home" page.
    $path = '/admin/config/security';
    $defaults = array(
      '_controller' => '\Drupal\system\Controller\SystemController::systemAdminMenuBlockPage',
      '_title' => 'Security',
    );
    $requirements = array(
        '_permission' => 'access administration pages'
    );
    $route = new Route($path, $defaults, $requirements);
    $collection->add('encryption_policy.admin_index', $route);
  }

  /**
   * Determine if requested path exists.
   *
   * @return bool
   */
  protected function pathExists($path, $debug = FALSE) {
    $route_collections = $this->routeProvider->getRoutesByPattern($path);

    if ($debug) {
      print_r($route_collections);
    }

    $path_exists = (count($route_collections) > 1) ? TRUE : FALSE;
    return $path_exists;
  }

}
