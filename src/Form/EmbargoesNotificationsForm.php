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
    $form = parent::buildForm($form, $form_state);

    $form['applications'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Application Notifications'),
    );
    $form['applications']['active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo application notification emails'),
      '#attributes' => [
        'name' => 'applications_checkbox',
      ],
    ); 
    $form['applications']['emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo application notification emails by default'),
      '#states' => [
        'visible' => [
          ':input[name="applications_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['applications']['template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Application notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo application notifications'),
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
    $form['updates']['active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo update notification emails'),
      '#attributes' => [
        'name' => 'updates_checkbox',
      ],
    ); 
    $form['updates']['emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo update notification emails by default'),
      '#states' => [
        'visible' => [
          ':input[name="updates_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['updates']['template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Update notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo update notifications'),
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
    $form['warnings']['active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo expiration warning notification emails'),
      '#attributes' => [
        'name' => 'warnings_checkbox',
      ],
    ); 
    $form['warnings']['period'] = array(
      '#type' => 'number',
      '#title' => $this->t('Days until expiry'),
      '#description' => $this->t('Enter the number of days before an embargo expires that an embargo expiration warning notification should be sent'),
      '#min' => 1,
      '#states' => [
        'visible' => [
          ':input[name="warnings_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['warnings']['emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration warning notification emails by default'),
      '#states' => [
        'visible' => [
          ':input[name="warnings_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['warnings']['template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Warning notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration warning notifications'),
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
    $form['expirations']['active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embargo expiration notification emails'),
      '#attributes' => [
        'name' => 'expirations_checkbox',
      ],
    ); 
    $form['expirations']['emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration notification emails by default'),
      '#states' => [
        'visible' => [
          ':input[name="expirations_checkbox"]' => ['checked' => TRUE],
        ],
      ],
    );
    $form['expirations']['template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Expiration notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration notifications'),
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
