<?php

namespace Drupal\embargoes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmbargoesSettingsForm.
 */
class EmbargoesNotificationsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'embargoes_notifications';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['embargoes.notifications'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('embargoes.notifications');
    $form = parent::buildForm($form, $form_state);


    $form['applications'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Application Notifications'),
    );
    $form['applications']['applications_active'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo application notification emails'),
      '#default_value' => $config->get('applications_active'),
      '#options' => [ 
        '0' => t('Disabled'),
        '1' => t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'applications_active',
      ],
    ); 
    $form['applications']['applications_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo application notification emails by default'),
      '#default_value' => $config->get('applications_emails'),
      '#states' => [
        'visible' => [
          ':input[name="applications_active"]' => ['value' => '1'],
        ],
      ],
    );
    $form['applications']['applications_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Application notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo application notifications'),
      '#default_value' => $config->get('applications_template'),
      '#states' => [
        'visible' => [
          ':input[name="applications_active"]' => ['value' => '1'],
        ],
      ],
    ); 

    $form['updates'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Update Notifications'),
    );
    $form['updates']['updates_active'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo update notification emails'),
      '#default_value' => $config->get('updates_active'),
      '#options' => [ 
        '0' => t('Disabled'),
        '1' => t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'updates_active',
      ],
    ); 
    $form['updates']['updates_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo update notification emails by default'),
      '#default_value' => $config->get('updates_emails'),
      '#states' => [
        'visible' => [
          ':input[name="updates_active"]' => ['value' => '1'],
        ],
      ],
    );
    $form['updates']['updates_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Update notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo update notifications'),
      '#default_value' => $config->get('updates_template'),
      '#states' => [
        'visible' => [
          ':input[name="updates_active"]' => ['value' => '1'],
        ],
      ],
    );

    $form['warnings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Expiration Warning Notifications'),
    );
    $form['warnings']['warnings_active'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo expiration warning notification emails'),
      '#default_value' => $config->get('warnings_active'),
      '#options' => [ 
        '0' => t('Disabled'),
        '1' => t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'warnings_active',
      ],
    ); 
    $form['warnings']['warnings_period'] = array(
      '#type' => 'number',
      '#title' => $this->t('Days until expiry'),
      '#description' => $this->t('Enter the number of days before an embargo expires that an embargo expiration warning notification should be sent'),
      '#default_value' => $config->get('warnings_period'),
      '#min' => 1,
      '#states' => [
        'visible' => [
          ':input[name="warnings_active"]' => ['value' => '1'],
        ],
      ],
    );
    $form['warnings']['warnings_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration warning notification emails by default'),
      '#default_value' => $config->get('warnings_emails'),
      '#states' => [
        'visible' => [
          ':input[name="warnings_active"]' => ['value' => '1'],
        ],
      ],
    );
    $form['warnings']['warnings_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Warning notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration warning notifications'),
      '#default_value' => $config->get('warnings_template'),
      '#states' => [
        'visible' => [
          ':input[name="warnings_active"]' => ['value' => '1'],
        ],
      ],
    ); 

    $form['expirations'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Expiration Notifications'),
    );
    $form['expirations']['expirations_active'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo expiration notification emails'),
      '#default_value' => $config->get('expirations_active'),
      '#options' => [ 
        '0' => t('Disabled'),
        '1' => t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'expirations_active',
      ],
    ); 
    $form['expirations']['expirations_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration notification emails by default'),
      '#default_value' => $config->get('expirations_emails'),
      '#states' => [
        'visible' => [
          ':input[name="expirations_active"]' => ['value' => '1'],
        ],
      ],
    );
    $form['expirations']['expirations_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Expiration notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration notifications'),
      '#default_value' => $config->get('expirations_template'),
      '#states' => [
        'visible' => [
          ':input[name="expirations_active"]' => ['value' => '1'],
        ],
      ],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('embargoes.notifications');

    $config->set('applications_active', $form_state->getValue('applications_active'));
    $config->set('applications_emails', $form_state->getValue('applications_emails'));
    $config->set('applications_template', $form_state->getValue('applications_template'));

    $config->set('updates_active', $form_state->getValue('updates_active'));
    $config->set('updates_emails', $form_state->getValue('updates_emails'));
    $config->set('updates_template', $form_state->getValue('updates_template'));

    $config->set('warnings_active', $form_state->getValue('warnings_active'));
    $config->set('warnings_period', $form_state->getValue('warnings_period'));
    $config->set('warnings_emails', $form_state->getValue('warnings_emails'));
    $config->set('warnings_template', $form_state->getValue('warnings_template'));

    $config->set('expirations_active', $form_state->getValue('expirations_active'));
    $config->set('expirations_emails', $form_state->getValue('expirations_emails'));
    $config->set('expirations_template', $form_state->getValue('expirations_template'));

    $config->save();

    parent::submitForm($form, $form_state);
  }

}
