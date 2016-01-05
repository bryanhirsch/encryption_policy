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
    // @TODO Figure out how to pass this in via dependency injection.
    $config = \Drupal::configFactory()->get('encryption_policy.settings');

    $build['encryption_policy_index']['#theme'] = 'encryption_policy_page';
    $build['encryption_policy_index']['#var1'] = 'Hello World';
    $build['encryption_policy_index']['#attached']['library'][] = 'encryption_policy/encryption-policy';

    /**
     * Leverage core table theme function for three of the four tables provided on /encryption-policy.
     *
     * Table theming works like this (for more details see system/templates/html.html.twig):
     *    $build['table1']['#theme'] = 'table';
     *    $build['table1']['#header'] = array('one', 'two', 'three');
     *    $build['table1']['#rows'][]['data'] = array('apple', 'banana', 'carrot');
     *    $build['table1']['#rows'][]['data'] = array('durian', 'eggplant', 'fig');
    // */

    // Generate up to three tables, explaining available, blacklist, and whitelist info to end-users.
    $tables = array(
      'available' => t('Cipher Suites Available'),
      'blacklist' => t('Cipher Suites Blacklist'),
      'whitelist' => t('Cipher Suites Whitelist'),
    );

    foreach ($tables as $key => $name) {
      $show_key = 'show_cipher_suites_' . $key;
      $machine_name = 'cipher_suites_' . $key;

      // Determine if administrator has opted not to show these policy details to end users. If not, don't include
      // these details in the render array being built here.
      $show = $config->get($show_key);
      if (!$show) {
        continue;
      }

      $cipher_suites = $config->get($machine_name);
      $build[$machine_name]['#theme'] = 'table';
      // $build[$machine_name]['#caption'] = 'testing 1 2 3'; // @TODO Add captions to make encryption policy easier for end users to understand.
      $build[$machine_name]['#header'] = array($name);
      foreach ($cipher_suites as $cipher_suite) {
        if (!empty($cipher_suite)) {
          $build[$machine_name]['#rows'][]['data'] = array($cipher_suite);
        }
      }

    }

    return $build;
  }

}
