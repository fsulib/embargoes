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
      '#default_value' => (!empty($config->get('embargo_contact_email')) ? $config->get('embargo_contact_email') : $this->config('system.site')->get('mail')),
    ];

    $form['add_contact_to_notifications'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add contact to notifications'),
      '#description' => $this->t('Add contact email to all embargo notifications by default.'),
      '#default_value' => (!is_null($config->get('add_contact_to_notifications')) ? $config->get('add_contact_to_notifications') : TRUE),
    ];

    $form['show_embargo_message'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show embargo message'),
      '#description' => $this->t('Show a Drupal warning message on nodes under active embargoes.'),
      '#default_value' => (!is_null($config->get('show_embargo_message')) ? $config->get('show_embargo_message') : TRUE),
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
    $config->save();
    parent::submitForm($form, $form_state);
    drupal_flush_all_caches();
  }

}
