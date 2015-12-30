<?php

/**
 * @file
 * Contains \Drupal\encryption_policy\Form\EncryptionPolicyForm.
 */

namespace Drupal\encryption_policy\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class EncryptionPolicyForm.
 *
 * @package Drupal\encryption_policy\Form
 */
class EncryptionPolicyForm extends ConfigFormBase {

  /**
   * Array listing ciphers.
   */
  private $ciphers;

  /**
   *
   */
  private $whitelistedCiphers;
  private $blacklistedCiphers;
  private $serverSideCiphers;

  /**
   * Constructs an EncryptionPolicyForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'encryption_policy_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['encryption_policy.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $cipher_suites_available = $this->settingsToString( $this->getCipherSuites() );
    $cipher_suites_blacklist = $this->settingsToString( $this->getCipherSuitesBlacklist() );
    $cipher_suites_whitelist = $this->settingsToString( $this->getCipherSuitesWhitelist() );

    $show_cipher_suites_available = $this->getShowCipherSuitesAvailable();
    $show_cipher_suites_blacklist = $this->getShowCipherSuitesBlacklist();
    $show_cipher_suites_whitelist = $this->getShowCipherSuitesWhitelist();

    // Define a list of cipher suites available on your server.
    $form['available'] = array(
      '#type' => 'details',
      '#title' => t('Cipher suites available'),
      '#description' => t('Enter a list of available cipher suites supported by your server here. Wherever your TLS/SSL connection terminates... @TODO finish explaination here.'),
    );
    $form['available']['cipher_suites_available'] = array(
      '#type' => 'textarea',
      '#title' => t('Cipher suites available on your server'),
      '#default_value' => $cipher_suites_available,
      '#description' => t('Defaults to a full list of TLS cipher suites'),
    );

    // Provide some helpful info about available cipher suites for users who
    // need help figuring out what to enter for the cipher_suites_available
    // setting.
    $form['available']['cipher_suite_check_link'] = array(
      '#type' => 'item',
      '#title' => '@TODO Enter links to sites that do checks here',
      '#description' => t('desc'),
    );
    $form['available']['cipher_suite_check_script'] = array(
      '#type' => 'item',
      '#title' => '@TODO Enter bash script here',
      '#description' => t('desc'),
    );

    // Blacklist cipher suites if there are any supported on your server that
    // you want to explicitly disallow.
    $form['blacklist'] = array(
      '#type' => 'details',
      '#title' => t('Cipher suites blacklist'),
      '#description' => t('@TODO'),
    );
    $form['blacklist']['cipher_suites_blacklist'] = array(
      '#type' => 'textarea',
      '#default_value' => $cipher_suites_blacklist,
      '#title' => t('Options'),
    );

    // Whitelist cipher suites if you only want to allow an explicit subset
    // of cipher suites supported by your server.
    $form['whitelist'] = array(
      '#type' => 'details',
      '#title' => t('Cipher suites whitelist'),
      '#description' => t('@TODO'),
    );
    $form['whitelist']['cipher_suites_whitelist'] = array(
      '#type' => 'textarea',
      '#default_value' => $cipher_suites_whitelist,
      '#title' => t('Options'),
    );

    // Enable administrator to configure display of public-facing /encryption-policy page.
    $link = Url::fromRoute('encryption_policy.encryption_policy_index');
    $form['encryption_policy'] = array(
      '#type' => 'details',
      '#title' => t('Encryption Policy'),
      '#description' => t('Configure how encryption policy is explained to end-users here: @link', array(
        '@link' => \Drupal::l('/encryption-policy', $link),
      )),
      '#open' => TRUE,
    );
    $form['encryption_policy']['show_cipher_suites_available'] = array(
        '#type' => 'checkbox',
        '#title' => t('Show cipher suites available'),
        '#description' => t('@TODO'),
        '#default_value' => $show_cipher_suites_available,
    );
    $form['encryption_policy']['show_cipher_suites_blacklist'] = array(
        '#type' => 'checkbox',
        '#title' => t('Show cipher suites blacklist'),
        '#description' => t('@TODO'),
        '#default_value' => $show_cipher_suites_blacklist,
    );
    $form['encryption_policy']['show_cipher_suites_whitelist'] = array(
        '#type' => 'checkbox',
        '#title' => t('Show cipher suites whitelist'),
        '#description' => t('@TODO'),
        '#default_value' => $show_cipher_suites_whitelist,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * Return array of cipher suite names.
   *
   * This list may correspond to cipher suites available on the server where the
   * TLS/SSL connection terminates, or it may just be a list of all TLS cipher
   * suites. We'll use the best info available.
   *
   * @return array
   */
  private function getCipherSuites() {
    // Use a specific list of available server-side ciphers if we have this.
    $cipher_suites_available = $this->getAvailableCipherSuites();
    if (count($cipher_suites_available) > 0) {
      return $cipher_suites_available;
    }

    // Otherwise use full list of TLS Ciphers.
    return $this->getAllTlsCipherSuites();
  }

  /**
   * Return an array of available server-side cipher suites.
   *
   * @return array
   *   Array is empty if no information is available.
   */
  private function getAvailableCipherSuites() {
    $ciphers = $this->config('encryption_policy.settings')->get('cipher_suites_available');
    return $this->settingsToArray($ciphers);
  }

  /**
   * Check type of $settings. Convert to empty array if not set as array yet.
   *
   * @param $settings
   * @return array
   */
  private function settingsToArray($settings) {
    $type = gettype($settings);

    if ($type == 'array') {
      return $settings;
    }

    if (empty($settings)) {
      return array();
    }

    return explode("\r\n", $settings);
  }

  private function settingsToString(array $settings) {
    return implode("\r\n", $settings);
  }

  /**
   * Returns an array of all cipher suites in the TLS standards.
   *
   * Based on:
   *   https://github.com/jmhodges/howsmyssl/blob/master/all_suites.go
   *
   * Generated with:
   *   curl -s http://www.iana.org/assignments/tls-parameters/tls-parameters.txt | grep '0x.* TLS_' | awk '{ print "\""$2"\","}' | sed 's/,0x//'
   *
   * Plus appending the new ChaCha20/Poly1305 curve ciphers from Chrome 33.0 and
   * the fallback SCSV if the client had to degrade its version of TLS in order
   * to talk to the server. This is currently only in Chrome, and may one day be
   * useful to call out.
   */
  public static function getAllTlsCipherSuites() {
    return array(
      "TLS_NULL_WITH_NULL_NULL",
      "TLS_RSA_WITH_NULL_MD5",
      "TLS_RSA_WITH_NULL_SHA",
      "TLS_RSA_EXPORT_WITH_RC4_40_MD5",
      "TLS_RSA_WITH_RC4_128_MD5",
      "TLS_RSA_WITH_RC4_128_SHA",
      "TLS_RSA_EXPORT_WITH_RC2_CBC_40_MD5",
      "TLS_RSA_WITH_IDEA_CBC_SHA",
      "TLS_RSA_EXPORT_WITH_DES40_CBC_SHA",
      "TLS_RSA_WITH_DES_CBC_SHA",
      "TLS_RSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_DH_DSS_EXPORT_WITH_DES40_CBC_SHA",
      "TLS_DH_DSS_WITH_DES_CBC_SHA",
      "TLS_DH_DSS_WITH_3DES_EDE_CBC_SHA",
      "TLS_DH_RSA_EXPORT_WITH_DES40_CBC_SHA",
      "TLS_DH_RSA_WITH_DES_CBC_SHA",
      "TLS_DH_RSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_DHE_DSS_EXPORT_WITH_DES40_CBC_SHA",
      "TLS_DHE_DSS_WITH_DES_CBC_SHA",
      "TLS_DHE_DSS_WITH_3DES_EDE_CBC_SHA",
      "TLS_DHE_RSA_EXPORT_WITH_DES40_CBC_SHA",
      "TLS_DHE_RSA_WITH_DES_CBC_SHA",
      "TLS_DHE_RSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_DH_anon_EXPORT_WITH_RC4_40_MD5",
      "TLS_DH_anon_WITH_RC4_128_MD5",
      "TLS_DH_anon_EXPORT_WITH_DES40_CBC_SHA",
      "TLS_DH_anon_WITH_DES_CBC_SHA",
      "TLS_DH_anon_WITH_3DES_EDE_CBC_SHA",
      "TLS_KRB5_WITH_DES_CBC_SHA",
      "TLS_KRB5_WITH_3DES_EDE_CBC_SHA",
      "TLS_KRB5_WITH_RC4_128_SHA",
      "TLS_KRB5_WITH_IDEA_CBC_SHA",
      "TLS_KRB5_WITH_DES_CBC_MD5",
      "TLS_KRB5_WITH_3DES_EDE_CBC_MD5",
      "TLS_KRB5_WITH_RC4_128_MD5",
      "TLS_KRB5_WITH_IDEA_CBC_MD5",
      "TLS_KRB5_EXPORT_WITH_DES_CBC_40_SHA",
      "TLS_KRB5_EXPORT_WITH_RC2_CBC_40_SHA",
      "TLS_KRB5_EXPORT_WITH_RC4_40_SHA",
      "TLS_KRB5_EXPORT_WITH_DES_CBC_40_MD5",
      "TLS_KRB5_EXPORT_WITH_RC2_CBC_40_MD5",
      "TLS_KRB5_EXPORT_WITH_RC4_40_MD5",
      "TLS_PSK_WITH_NULL_SHA",
      "TLS_DHE_PSK_WITH_NULL_SHA",
      "TLS_RSA_PSK_WITH_NULL_SHA",
      "TLS_RSA_WITH_AES_128_CBC_SHA",
      "TLS_DH_DSS_WITH_AES_128_CBC_SHA",
      "TLS_DH_RSA_WITH_AES_128_CBC_SHA",
      "TLS_DHE_DSS_WITH_AES_128_CBC_SHA",
      "TLS_DHE_RSA_WITH_AES_128_CBC_SHA",
      "TLS_DH_anon_WITH_AES_128_CBC_SHA",
      "TLS_RSA_WITH_AES_256_CBC_SHA",
      "TLS_DH_DSS_WITH_AES_256_CBC_SHA",
      "TLS_DH_RSA_WITH_AES_256_CBC_SHA",
      "TLS_DHE_DSS_WITH_AES_256_CBC_SHA",
      "TLS_DHE_RSA_WITH_AES_256_CBC_SHA",
      "TLS_DH_anon_WITH_AES_256_CBC_SHA",
      "TLS_RSA_WITH_NULL_SHA256",
      "TLS_RSA_WITH_AES_128_CBC_SHA256",
      "TLS_RSA_WITH_AES_256_CBC_SHA256",
      "TLS_DH_DSS_WITH_AES_128_CBC_SHA256",
      "TLS_DH_RSA_WITH_AES_128_CBC_SHA256",
      "TLS_DHE_DSS_WITH_AES_128_CBC_SHA256",
      "TLS_RSA_WITH_CAMELLIA_128_CBC_SHA",
      "TLS_DH_DSS_WITH_CAMELLIA_128_CBC_SHA",
      "TLS_DH_RSA_WITH_CAMELLIA_128_CBC_SHA",
      "TLS_DHE_DSS_WITH_CAMELLIA_128_CBC_SHA",
      "TLS_DHE_RSA_WITH_CAMELLIA_128_CBC_SHA",
      "TLS_DH_anon_WITH_CAMELLIA_128_CBC_SHA",
      "TLS_DHE_RSA_WITH_AES_128_CBC_SHA256",
      "TLS_DH_DSS_WITH_AES_256_CBC_SHA256",
      "TLS_DH_RSA_WITH_AES_256_CBC_SHA256",
      "TLS_DHE_DSS_WITH_AES_256_CBC_SHA256",
      "TLS_DHE_RSA_WITH_AES_256_CBC_SHA256",
      "TLS_DH_anon_WITH_AES_128_CBC_SHA256",
      "TLS_DH_anon_WITH_AES_256_CBC_SHA256",
      "TLS_RSA_WITH_CAMELLIA_256_CBC_SHA",
      "TLS_DH_DSS_WITH_CAMELLIA_256_CBC_SHA",
      "TLS_DH_RSA_WITH_CAMELLIA_256_CBC_SHA",
      "TLS_DHE_DSS_WITH_CAMELLIA_256_CBC_SHA",
      "TLS_DHE_RSA_WITH_CAMELLIA_256_CBC_SHA",
      "TLS_DH_anon_WITH_CAMELLIA_256_CBC_SHA",
      "TLS_PSK_WITH_RC4_128_SHA",
      "TLS_PSK_WITH_3DES_EDE_CBC_SHA",
      "TLS_PSK_WITH_AES_128_CBC_SHA",
      "TLS_PSK_WITH_AES_256_CBC_SHA",
      "TLS_DHE_PSK_WITH_RC4_128_SHA",
      "TLS_DHE_PSK_WITH_3DES_EDE_CBC_SHA",
      "TLS_DHE_PSK_WITH_AES_128_CBC_SHA",
      "TLS_DHE_PSK_WITH_AES_256_CBC_SHA",
      "TLS_RSA_PSK_WITH_RC4_128_SHA",
      "TLS_RSA_PSK_WITH_3DES_EDE_CBC_SHA",
      "TLS_RSA_PSK_WITH_AES_128_CBC_SHA",
      "TLS_RSA_PSK_WITH_AES_256_CBC_SHA",
      "TLS_RSA_WITH_SEED_CBC_SHA",
      "TLS_DH_DSS_WITH_SEED_CBC_SHA",
      "TLS_DH_RSA_WITH_SEED_CBC_SHA",
      "TLS_DHE_DSS_WITH_SEED_CBC_SHA",
      "TLS_DHE_RSA_WITH_SEED_CBC_SHA",
      "TLS_DH_anon_WITH_SEED_CBC_SHA",
      "TLS_RSA_WITH_AES_128_GCM_SHA256",
      "TLS_RSA_WITH_AES_256_GCM_SHA384",
      "TLS_DHE_RSA_WITH_AES_128_GCM_SHA256",
      "TLS_DHE_RSA_WITH_AES_256_GCM_SHA384",
      "TLS_DH_RSA_WITH_AES_128_GCM_SHA256",
      "TLS_DH_RSA_WITH_AES_256_GCM_SHA384",
      "TLS_DHE_DSS_WITH_AES_128_GCM_SHA256",
      "TLS_DHE_DSS_WITH_AES_256_GCM_SHA384",
      "TLS_DH_DSS_WITH_AES_128_GCM_SHA256",
      "TLS_DH_DSS_WITH_AES_256_GCM_SHA384",
      "TLS_DH_anon_WITH_AES_128_GCM_SHA256",
      "TLS_DH_anon_WITH_AES_256_GCM_SHA384",
      "TLS_PSK_WITH_AES_128_GCM_SHA256",
      "TLS_PSK_WITH_AES_256_GCM_SHA384",
      "TLS_DHE_PSK_WITH_AES_128_GCM_SHA256",
      "TLS_DHE_PSK_WITH_AES_256_GCM_SHA384",
      "TLS_RSA_PSK_WITH_AES_128_GCM_SHA256",
      "TLS_RSA_PSK_WITH_AES_256_GCM_SHA384",
      "TLS_PSK_WITH_AES_128_CBC_SHA256",
      "TLS_PSK_WITH_AES_256_CBC_SHA384",
      "TLS_PSK_WITH_NULL_SHA256",
      "TLS_PSK_WITH_NULL_SHA384",
      "TLS_DHE_PSK_WITH_AES_128_CBC_SHA256",
      "TLS_DHE_PSK_WITH_AES_256_CBC_SHA384",
      "TLS_DHE_PSK_WITH_NULL_SHA256",
      "TLS_DHE_PSK_WITH_NULL_SHA384",
      "TLS_RSA_PSK_WITH_AES_128_CBC_SHA256",
      "TLS_RSA_PSK_WITH_AES_256_CBC_SHA384",
      "TLS_RSA_PSK_WITH_NULL_SHA256",
      "TLS_RSA_PSK_WITH_NULL_SHA384",
      "TLS_RSA_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_DH_DSS_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_DH_RSA_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_DHE_DSS_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_DHE_RSA_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_DH_anon_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_RSA_WITH_CAMELLIA_256_CBC_SHA256",
      "TLS_DH_DSS_WITH_CAMELLIA_256_CBC_SHA256",
      "TLS_DH_RSA_WITH_CAMELLIA_256_CBC_SHA256",
      "TLS_DHE_DSS_WITH_CAMELLIA_256_CBC_SHA256",
      "TLS_DHE_RSA_WITH_CAMELLIA_256_CBC_SHA256",
      "TLS_DH_anon_WITH_CAMELLIA_256_CBC_SHA256",
      "TLS_EMPTY_RENEGOTIATION_INFO_SCSV",
      "TLS_FALLBACK_SCSV",
      "TLS_ECDH_ECDSA_WITH_NULL_SHA",
      "TLS_ECDH_ECDSA_WITH_RC4_128_SHA",
      "TLS_ECDH_ECDSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_ECDH_ECDSA_WITH_AES_128_CBC_SHA",
      "TLS_ECDH_ECDSA_WITH_AES_256_CBC_SHA",
      "TLS_ECDHE_ECDSA_WITH_NULL_SHA",
      "TLS_ECDHE_ECDSA_WITH_RC4_128_SHA",
      "TLS_ECDHE_ECDSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA",
      "TLS_ECDHE_ECDSA_WITH_AES_256_CBC_SHA",
      "TLS_ECDH_RSA_WITH_NULL_SHA",
      "TLS_ECDH_RSA_WITH_RC4_128_SHA",
      "TLS_ECDH_RSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_ECDH_RSA_WITH_AES_128_CBC_SHA",
      "TLS_ECDH_RSA_WITH_AES_256_CBC_SHA",
      "TLS_ECDHE_RSA_WITH_NULL_SHA",
      "TLS_ECDHE_RSA_WITH_RC4_128_SHA",
      "TLS_ECDHE_RSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_ECDHE_RSA_WITH_AES_128_CBC_SHA",
      "TLS_ECDHE_RSA_WITH_AES_256_CBC_SHA",
      "TLS_ECDH_anon_WITH_NULL_SHA",
      "TLS_ECDH_anon_WITH_RC4_128_SHA",
      "TLS_ECDH_anon_WITH_3DES_EDE_CBC_SHA",
      "TLS_ECDH_anon_WITH_AES_128_CBC_SHA",
      "TLS_ECDH_anon_WITH_AES_256_CBC_SHA",
      "TLS_SRP_SHA_WITH_3DES_EDE_CBC_SHA",
      "TLS_SRP_SHA_RSA_WITH_3DES_EDE_CBC_SHA",
      "TLS_SRP_SHA_DSS_WITH_3DES_EDE_CBC_SHA",
      "TLS_SRP_SHA_WITH_AES_128_CBC_SHA",
      "TLS_SRP_SHA_RSA_WITH_AES_128_CBC_SHA",
      "TLS_SRP_SHA_DSS_WITH_AES_128_CBC_SHA",
      "TLS_SRP_SHA_WITH_AES_256_CBC_SHA",
      "TLS_SRP_SHA_RSA_WITH_AES_256_CBC_SHA",
      "TLS_SRP_SHA_DSS_WITH_AES_256_CBC_SHA",
      "TLS_ECDHE_ECDSA_WITH_AES_128_CBC_SHA256",
      "TLS_ECDHE_ECDSA_WITH_AES_256_CBC_SHA384",
      "TLS_ECDH_ECDSA_WITH_AES_128_CBC_SHA256",
      "TLS_ECDH_ECDSA_WITH_AES_256_CBC_SHA384",
      "TLS_ECDHE_RSA_WITH_AES_128_CBC_SHA256",
      "TLS_ECDHE_RSA_WITH_AES_256_CBC_SHA384",
      "TLS_ECDH_RSA_WITH_AES_128_CBC_SHA256",
      "TLS_ECDH_RSA_WITH_AES_256_CBC_SHA384",
      "TLS_ECDHE_ECDSA_WITH_AES_128_GCM_SHA256",
      "TLS_ECDHE_ECDSA_WITH_AES_256_GCM_SHA384",
      "TLS_ECDH_ECDSA_WITH_AES_128_GCM_SHA256",
      "TLS_ECDH_ECDSA_WITH_AES_256_GCM_SHA384",
      "TLS_ECDHE_RSA_WITH_AES_128_GCM_SHA256",
      "TLS_ECDHE_RSA_WITH_AES_256_GCM_SHA384",
      "TLS_ECDH_RSA_WITH_AES_128_GCM_SHA256",
      "TLS_ECDH_RSA_WITH_AES_256_GCM_SHA384",
      "TLS_ECDHE_PSK_WITH_RC4_128_SHA",
      "TLS_ECDHE_PSK_WITH_3DES_EDE_CBC_SHA",
      "TLS_ECDHE_PSK_WITH_AES_128_CBC_SHA",
      "TLS_ECDHE_PSK_WITH_AES_256_CBC_SHA",
      "TLS_ECDHE_PSK_WITH_AES_128_CBC_SHA256",
      "TLS_ECDHE_PSK_WITH_AES_256_CBC_SHA384",
      "TLS_ECDHE_PSK_WITH_NULL_SHA",
      "TLS_ECDHE_PSK_WITH_NULL_SHA256",
      "TLS_ECDHE_PSK_WITH_NULL_SHA384",
      "TLS_RSA_WITH_ARIA_128_CBC_SHA256",
      "TLS_RSA_WITH_ARIA_256_CBC_SHA384",
      "TLS_DH_DSS_WITH_ARIA_128_CBC_SHA256",
      "TLS_DH_DSS_WITH_ARIA_256_CBC_SHA384",
      "TLS_DH_RSA_WITH_ARIA_128_CBC_SHA256",
      "TLS_DH_RSA_WITH_ARIA_256_CBC_SHA384",
      "TLS_DHE_DSS_WITH_ARIA_128_CBC_SHA256",
      "TLS_DHE_DSS_WITH_ARIA_256_CBC_SHA384",
      "TLS_DHE_RSA_WITH_ARIA_128_CBC_SHA256",
      "TLS_DHE_RSA_WITH_ARIA_256_CBC_SHA384",
      "TLS_DH_anon_WITH_ARIA_128_CBC_SHA256",
      "TLS_DH_anon_WITH_ARIA_256_CBC_SHA384",
      "TLS_ECDHE_ECDSA_WITH_ARIA_128_CBC_SHA256",
      "TLS_ECDHE_ECDSA_WITH_ARIA_256_CBC_SHA384",
      "TLS_ECDH_ECDSA_WITH_ARIA_128_CBC_SHA256",
      "TLS_ECDH_ECDSA_WITH_ARIA_256_CBC_SHA384",
      "TLS_ECDHE_RSA_WITH_ARIA_128_CBC_SHA256",
      "TLS_ECDHE_RSA_WITH_ARIA_256_CBC_SHA384",
      "TLS_ECDH_RSA_WITH_ARIA_128_CBC_SHA256",
      "TLS_ECDH_RSA_WITH_ARIA_256_CBC_SHA384",
      "TLS_RSA_WITH_ARIA_128_GCM_SHA256",
      "TLS_RSA_WITH_ARIA_256_GCM_SHA384",
      "TLS_DHE_RSA_WITH_ARIA_128_GCM_SHA256",
      "TLS_DHE_RSA_WITH_ARIA_256_GCM_SHA384",
      "TLS_DH_RSA_WITH_ARIA_128_GCM_SHA256",
      "TLS_DH_RSA_WITH_ARIA_256_GCM_SHA384",
      "TLS_DHE_DSS_WITH_ARIA_128_GCM_SHA256",
      "TLS_DHE_DSS_WITH_ARIA_256_GCM_SHA384",
      "TLS_DH_DSS_WITH_ARIA_128_GCM_SHA256",
      "TLS_DH_DSS_WITH_ARIA_256_GCM_SHA384",
      "TLS_DH_anon_WITH_ARIA_128_GCM_SHA256",
      "TLS_DH_anon_WITH_ARIA_256_GCM_SHA384",
      "TLS_ECDHE_ECDSA_WITH_ARIA_128_GCM_SHA256",
      "TLS_ECDHE_ECDSA_WITH_ARIA_256_GCM_SHA384",
      "TLS_ECDH_ECDSA_WITH_ARIA_128_GCM_SHA256",
      "TLS_ECDH_ECDSA_WITH_ARIA_256_GCM_SHA384",
      "TLS_ECDHE_RSA_WITH_ARIA_128_GCM_SHA256",
      "TLS_ECDHE_RSA_WITH_ARIA_256_GCM_SHA384",
      "TLS_ECDH_RSA_WITH_ARIA_128_GCM_SHA256",
      "TLS_ECDH_RSA_WITH_ARIA_256_GCM_SHA384",
      "TLS_PSK_WITH_ARIA_128_CBC_SHA256",
      "TLS_PSK_WITH_ARIA_256_CBC_SHA384",
      "TLS_DHE_PSK_WITH_ARIA_128_CBC_SHA256",
      "TLS_DHE_PSK_WITH_ARIA_256_CBC_SHA384",
      "TLS_RSA_PSK_WITH_ARIA_128_CBC_SHA256",
      "TLS_RSA_PSK_WITH_ARIA_256_CBC_SHA384",
      "TLS_PSK_WITH_ARIA_128_GCM_SHA256",
      "TLS_PSK_WITH_ARIA_256_GCM_SHA384",
      "TLS_DHE_PSK_WITH_ARIA_128_GCM_SHA256",
      "TLS_DHE_PSK_WITH_ARIA_256_GCM_SHA384",
      "TLS_RSA_PSK_WITH_ARIA_128_GCM_SHA256",
      "TLS_RSA_PSK_WITH_ARIA_256_GCM_SHA384",
      "TLS_ECDHE_PSK_WITH_ARIA_128_CBC_SHA256",
      "TLS_ECDHE_PSK_WITH_ARIA_256_CBC_SHA384",
      "TLS_ECDHE_ECDSA_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_ECDHE_ECDSA_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_ECDH_ECDSA_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_ECDH_ECDSA_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_ECDHE_RSA_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_ECDHE_RSA_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_ECDH_RSA_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_ECDH_RSA_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_RSA_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_RSA_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_DHE_RSA_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_DHE_RSA_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_DH_RSA_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_DH_RSA_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_DHE_DSS_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_DHE_DSS_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_DH_DSS_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_DH_DSS_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_DH_anon_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_DH_anon_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_ECDHE_ECDSA_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_ECDHE_ECDSA_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_ECDH_ECDSA_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_ECDH_ECDSA_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_ECDHE_RSA_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_ECDHE_RSA_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_ECDH_RSA_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_ECDH_RSA_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_PSK_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_PSK_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_DHE_PSK_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_DHE_PSK_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_RSA_PSK_WITH_CAMELLIA_128_GCM_SHA256",
      "TLS_RSA_PSK_WITH_CAMELLIA_256_GCM_SHA384",
      "TLS_PSK_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_PSK_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_DHE_PSK_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_DHE_PSK_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_RSA_PSK_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_RSA_PSK_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_ECDHE_PSK_WITH_CAMELLIA_128_CBC_SHA256",
      "TLS_ECDHE_PSK_WITH_CAMELLIA_256_CBC_SHA384",
      "TLS_RSA_WITH_AES_128_CCM",
      "TLS_RSA_WITH_AES_256_CCM",
      "TLS_DHE_RSA_WITH_AES_128_CCM",
      "TLS_DHE_RSA_WITH_AES_256_CCM",
      "TLS_RSA_WITH_AES_128_CCM_8",
      "TLS_RSA_WITH_AES_256_CCM_8",
      "TLS_DHE_RSA_WITH_AES_128_CCM_8",
      "TLS_DHE_RSA_WITH_AES_256_CCM_8",
      "TLS_PSK_WITH_AES_128_CCM",
      "TLS_PSK_WITH_AES_256_CCM",
      "TLS_DHE_PSK_WITH_AES_128_CCM",
      "TLS_DHE_PSK_WITH_AES_256_CCM",
      "TLS_PSK_WITH_AES_128_CCM_8",
      "TLS_PSK_WITH_AES_256_CCM_8",
      "TLS_PSK_DHE_WITH_AES_128_CCM_8",
      "TLS_PSK_DHE_WITH_AES_256_CCM_8",
      "TLS_ECDHE_ECDSA_WITH_AES_128_CCM",
      "TLS_ECDHE_ECDSA_WITH_AES_256_CCM",
      "TLS_ECDHE_ECDSA_WITH_AES_128_CCM_8",
      "TLS_ECDHE_ECDSA_WITH_AES_256_CCM_8",

      "TLS_FALLBACK_SCSV",
      "TLS_ECDHE_RSA_WITH_CHACHA20_POLY1305_SHA256",
      "TLS_ECDHE_ECDSA_WITH_CHACHA20_POLY1305_SHA256",
      "TLS_DHE_RSA_WITH_CHACHA20_POLY1305_SHA256",

      // http://tools.ietf.org/html/draft-ietf-tls-56-bit-ciphersuites-01

      "TLS_DHE_DSS_EXPORT1024_WITH_DES_CBC_SHA",
      "TLS_RSA_EXPORT1024_WITH_RC4_56_SHA",
      "TLS_DHE_DSS_EXPORT1024_WITH_RC4_56_SHA",
      "TLS_DHE_DSS_WITH_RC4_128_SHA", // 128-bit RC4, not 56-bit
    );
  }

  private function getCipherSuitesBlackList() {
    $ciphers = $this->config('encryption_policy.settings')->get('cipher_suites_blacklist');
    return $this->settingsToArray($ciphers);
  }

  private function getCipherSuitesWhitelist() {
    $ciphers = $this->config('encryption_policy.settings')->get('cipher_suites_whitelist');
    return $this->settingsToArray($ciphers);
  }

  private function getShowCipherSuitesAvailable() {
    return $this->config('encryption_policy.settings')->get('show_cipher_suites_available');
  }

  private function getShowCipherSuitesBlacklist() {
    return $this->config('encryption_policy.settings')->get('show_cipher_suites_blacklist');
  }

  private function getShowCipherSuitesWhitelist() {
    return $this->config('encryption_policy.settings')->get('show_cipher_suites_whitelist');
  }

  /**
   * @TODO add form validation.
   *
   * Errors:
   * [ ] Do not allow a cipher to be listed on both blacklist and whitelist
   *
   * Warnings:
   * [ ] Nothing in ciphersuites available box
   */

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $variables = array('available', 'blacklist', 'whitelist');

    // Store textarea input. Make each line an item in an array.
    foreach ($variables as $variable) {
      $key = 'cipher_suites_' . $variable;
      $settings = $this->settingsToArray($values[$key]);
      $this->config('encryption_policy.settings')
        ->set($key, $settings)
        ->save();
    }

    // Store checkbox input. Values are stored as 0 or 1.
    foreach ($variables as $variable) {
      $key = 'show_cipher_suites_' . $variable;
      $value = $values[$key];
      $this->config('encryption_policy.settings')
          ->set($key, $value)
          ->save();
    }

    parent::submitForm($form, $form_state);
  }


}
