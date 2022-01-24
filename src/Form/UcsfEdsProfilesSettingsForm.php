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

    // $form['email_fn'] = [
    //   '#type' => 'textfield',
    //   '#title' => $this->t('Email field machine name'),
    //   '#default_value' => $config->get('email_fn'),
    //   '#description' => $this->t('The email field on ucsf_eds_profiles content type is required for EDS ldap search.'),
    //   '#required' => TRUE,
    // ];

    $form['ldap_mn'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Ldap server machine name'),
      '#default_value' => $config->get('ldap_mn'),
      '#description' => $this->t('The ldap server for EDS ldap search.'),
      '#required' => TRUE,
    ];

    $form['cron_delta'] = [
      '#type' => 'number',
      '#title' => $this->t('Cron delta'),
      '#default_value' => $config->get('cron_delta'),
      '#description' => $this->t('Minimum increment of time in seconds between cron sync of EDS/Profiles nodes with source.'),
      '#min' => 0,
      '#required' => TRUE,
    ];

    for($i=1;$i<10;$i++) {
      $options[$i] = $i;
    }
    $form['allowed_release_codes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed release codes'),
      '#options' => $options,
      '#default_value' => $config->get('allowed_release_codes'),
      '#description' => $this->t('Select the release codes that are allowed to store data.<br>UCSF levels of release indicates the protection level of the corresponding data for a person.<br>See https://wiki.library.ucsf.edu/display/IAM/Level+of+Release for release code descriptions.'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * Returns an array of settings keys.
   */
  public function settingsKeys() {
    return [
      // 'email_fn',
      'ldap_mn',
      'cron_delta',
      'allowed_release_codes',
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
