<?php

namespace Drupal\ucsf_eds_profiles\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UcsfEdsProfilesSettingsForm.
 */
class UcsfEdsProfilesSettingsForm extends ConfigFormBase {

  use MessengerTrait;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'ucsf_eds_profiles.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ucsf_eds_profiles_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['ldap_mn'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ldap server machine name'),
      '#default_value' => $config->get('ldap_mn'),
      '#description' => $this->t('The ldap server for EDS ldap search.'),
      '#required' => TRUE,
    ];

    $form['email_fn'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Email field machine name'),
      '#default_value' => $config->get('email_fn'),
      '#description' => $this->t('The email field on ucsf_eds_profiles content type is required for EDS ldap search.'),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Returns an array of settings keys.
   */
  public function settingsKeys() {
    return [
      'email_fn',
      'ldap_mn',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    foreach ($this->settingsKeys() as $key) {
      $config->set($key, $form_state->getValue($key));
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }
}
