<?php

/**
 * @file
 * Contains \Drupal\encryption_policy\Form\EncryptionPolicyForm.
 */

namespace Drupal\encryption_policy\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EncryptionPolicyForm.
 *
 * @package Drupal\encryption_policy\Form
 */
class EncryptionPolicyForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'encryption_policy_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
