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

    $form['redirect_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URL'),
      '#description' => $this->t('URL that user is to be redirected to (including parameters) if attempting to access IP restricted content from outside of an approved range.'),
      '#default_value' => $config->get('redirect_url'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('embargoes.settings');

    $config->set('redirect_url', $form_state->getValue('redirect_url'));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
