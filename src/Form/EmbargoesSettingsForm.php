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

    $content_types_formatted = [];
    $content_types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
    foreach ($content_types as $content_type) {
      $content_types_formatted[$content_type->get('type')] = $content_type->get('name');
    }

    $form['embargoable_content_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable embargoes by content type'),
      '#description' => $this->t("Select content types that should be able to be embargoed"),
      '#default_value' => $config->get('embargoable_content_types'),
      '#options' => $content_types_formatted,
    ];

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

    $config->set('embargoable_content_types', $form_state->getValue('embargoable_content_types'));
    $config->set('redirect_url', $form_state->getValue('redirect_url'));
    
    $config->save();
    parent::submitForm($form, $form_state);
    $this::manageEmbargoFieldAttachments($config);
  }

  public function manageEmbargoFieldAttachments($config) {
    foreach ($config->get('embargoable_content_types') as $key => $value) {
      if ($value != '0' && $this::contentTypeHasEmbargoField($key) == FALSE) {
        $this::addEmbargoFieldToContentType($key);
      }
      else if ($value == '0' && $this::contentTypeHasEmbargoField($key) == TRUE) {
        $this::removeEmbargoFieldfromContentType($key);
      }
    }
  }

  public function addEmbargoFieldToContentType($content_type) {
    dsm("Adding embargo field to {$content_type}");
  }

  public function removeEmbargoFieldFromContentType($content_type) {
    dsm("Removing embargo field from {$content_type}");
  }

  public function contentTypeHasEmbargoField($content_type) {
    return TRUE;
  }

}

  
