<?php

/**
 * @file
 * Contains \Drupal\encryption_policy\Tests\EncryptionPolicyPageController.
 */

namespace Drupal\encryption_policy\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the encryption_policy module.
 */
class EncryptionPolicyPageControllerTest extends WebTestBase {
  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => "encryption_policy EncryptionPolicyPageController's controller functionality",
      'description' => 'Test Unit for module encryption_policy and controller EncryptionPolicyPageController.',
      'group' => 'Other',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests encryption_policy functionality.
   */
  public function testEncryptionPolicyPageController() {
    // Check that the basic functions of module encryption_policy.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via App Console.');
  }

}
