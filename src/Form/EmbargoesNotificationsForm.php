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

    $form['warnings'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Warnings'),
    );

    $form['warnings']['active'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Turn on email warning notifications'),
    ); 

    $form['warnings']['emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Default warnings email recipients'),
    );


    $form['warnings']['email_template'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Warning email template'),
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
