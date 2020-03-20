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
    $form['enable_all_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Embargoes for all content?'),
      '#description' => $this->t("Enables embargo policies to be applied to all existing content types."),
      '#default_value' => $config->get('enable_all_content'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('embargoes.settings');

    $conf_enable_all_content = $config->get('enable_all_content');
    $form_enable_all_content = $form_state->getValue('enable_all_content');

    if ($conf_enable_all_content != $form_enable_all_content) {
      $config->set('enable_all_content', $form_enable_all_content)->save();
      \Drupal::service('router.builder')->setRebuildNeeded();
    }

    parent::submitForm($form, $form_state);
  }

}
