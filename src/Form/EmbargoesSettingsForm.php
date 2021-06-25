<?php

namespace Drupal\embargoes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmbargoesSettingsForm.
 */
class EmbargoesSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'embargoes_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['embargoes.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('embargoes.settings');

    $form['embargo_contact_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Contact Email'),
      '#description' => $this->t('Email address for who should be contacted in case users have questions about access.'),
      '#default_value' => $config->get('embargo_contact_email'),
    ];

    $form['add_contact_to_notifications'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add contact to notifications'),
      '#description' => $this->t('Add contact email to all embargo notifications by default.'),
      '#default_value' => $config->get('add_contact_to_notifications'),
    ];

    $form['show_embargo_message'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show embargo message'),
      '#description' => $this->t('Show a Drupal warning message on nodes under active embargoes.'),
      '#default_value' => $config->get('show_embargo_message'),
    ];

    $form['embargo_notification_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Embargo notification messsage.'),
      '#description' => $this->t('Notification text displayed to the user when an object or its files are under embargo. Use the "@contact" string to include the configured contact email, if available.'),
      '#default_value' => $config->get('embargo_notification_message'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('embargoes.settings');
    $config->set('show_embargo_message', $form_state->getValue('show_embargo_message'));
    $config->set('add_contact_to_notifications', $form_state->getValue('add_contact_to_notifications'));
    $config->set('embargo_contact_email', $form_state->getValue('embargo_contact_email'));
    $config->set('embargo_notification_message', $form_state->getValue('embargo_notification_message'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
