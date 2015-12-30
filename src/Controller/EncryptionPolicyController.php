<?php

/**
 * @file
 * Contains \Drupal\encryption_policy\Controller\EncryptionPolicyPageController.
 */

namespace Drupal\encryption_policy\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class EncryptionPolicyPageController.
 *
 * @package Drupal\encryption_policy\Controller
 */
class EncryptionPolicyController extends ControllerBase {
  /**
   * Returns output for /encryption-policy.
   *
   * @return array
   *   Renderable array
   */
  public function index() {
   $build = array();

   $build['encryption_policy_index']['#theme'] = 'encryption_policy_page';
   $build['encryption_policy_index']['#attached']['library'][] = 'encryption_policy/encryption-policy';

    return $build;
  }

}
