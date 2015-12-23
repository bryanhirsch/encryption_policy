<?php

/**
 * @file
 * Contains \Drupal\encryption_policy\Routing\RouteSubscriber.
 */

namespace Drupal\encryption_policy\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class RouteSubscriber.
 *
 * @package Drupal\encryption_policy\Routing
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
  }
}
