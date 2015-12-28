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
   * Index.
   *
   * @return array
   *   Renderable array
   */
  public function index() {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('hello world'),
        '#attached' => array(
          'library' => array(
            'encryption_policy/encryption-policy',
          ),
        ),
    ];
  }

}
