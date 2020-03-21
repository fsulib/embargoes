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
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo application notification emails'),
      '#default_value' => $config->get('applications_active'),
      '#attributes' => [
        'name' => 'applications_checkbox',
      ],
    ); 
    $form['applications']['applications_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo application notification emails by default'),
      '#default_value' => $config->get('applications_default_email_addresses'),
      '#states' => [
        'visible' => [
          ':input[name="applications_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['applications']['applications_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Application notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo application notifications'),
      '#default_value' => $config->get('applications_email_template'),
      '#states' => [
        'visible' => [
          ':input[name="applications_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    ); 

    $form['updates'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Update Notifications'),
    );
    $form['updates']['updates_active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo update notification emails'),
      //'#default_value' => $config->get('updates_active'),
      '#attributes' => [
        'name' => 'updates_checkbox',
      ],
    ); 
    $form['updates']['updates_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo update notification emails by default'),
      '#default_value' => $config->get('updates_default_email_addresses'),
      '#states' => [
        'visible' => [
          ':input[name="updates_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['updates']['updates_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Update notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo update notifications'),
      '#default_value' => $config->get('updates_email_template'),
      '#states' => [
        'visible' => [
          ':input[name="updates_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );

    $form['warnings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Expiration Warning Notifications'),
    );
    $form['warnings']['warnings_active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo expiration warning notification emails'),
      //'#default_value' => $config->get('warnings_active'),
      '#attributes' => [
        'name' => 'warnings_checkbox',
      ],
    ); 
    $form['warnings']['warnings_period'] = array(
      '#type' => 'number',
      '#title' => $this->t('Days until expiry'),
      '#description' => $this->t('Enter the number of days before an embargo expires that an embargo expiration warning notification should be sent'),
      '#default_value' => $config->get('warnings_expiry_period'),
      '#min' => 1,
      '#states' => [
        'visible' => [
          ':input[name="warnings_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['warnings']['warnings_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration warning notification emails by default'),
      '#default_value' => $config->get('warnings_default_email_addresses'),
      '#states' => [
        'visible' => [
          ':input[name="warnings_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['warnings']['warnings_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Warning notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration warning notifications'),
      //'#default_value' => $config->get('warnings_email_template'),
      '#states' => [
        'visible' => [
          ':input[name="warnings_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    ); 

    $form['expirations'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Expiration Notifications'),
    );
    $form['expirations']['expirations_active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo expiration notification emails'),
      '#default_value' => $config->get('expirations_active'),
      '#attributes' => [
        'name' => 'expirations_checkbox',
      ],
    ); 
    $form['expirations']['expirations_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration notification emails by default'),
      '#default_value' => $config->get('expirations_default_email_addresses'),
      '#states' => [
        'visible' => [
          ':input[name="expirations_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['expirations']['expirations_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Expiration notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration notifications'),
      '#default_value' => $config->get('expirations_email_template'),
      '#states' => [
        'visible' => [
          ':input[name="expirations_checkbox"]' => ['checked' => TRUE],
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

    parent::submitForm($form, $form_state);
  }

}
