<?php

namespace Drupal\embargoes\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmbargoesIpRangeEntityForm.
 */
class EmbargoesIpRangeEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $range = $this->entity;

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $range->id(),
      '#machine_name' => [
        'exists' => '\Drupal\embargoes\Entity\EmbargoesIpRangeEntity::load',
      ],
      '#disabled' => !$range->isNew(),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $range->label(),
      '#description' => $this->t("Label for the IP range."),
      '#required' => TRUE,
    ];

    $form['range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Range'),
      '#maxlength' => 255,
      '#default_value' => $range->getRange(),
      '#description' => $this->t("IP range to be used. Please list in CIDR format, and separate multiple ranges with a '|'."),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $range = $this->entity;
    $range->setRange($form_state->getValue('range'));
    $status = $range->save();
    
    $errors = \Drupal::service('embargoes.ips')->detectIpRangeStringErrors($form_state->getValue('range'));
    if (!$errors) {
      switch ($status) {
        case SAVED_NEW:
          $this->messenger()->addMessage($this->t('Created the %label IP Range.', ['%label' => $range->label()]));
          break;
        default:
          $this->messenger()->addMessage($this->t('Saved the %label IP Range.', ['%label' => $range->label()]));
      }
    }
    else {
      drupal_set_message("Problems detected with the {$range->label()} IP Range.", 'error');
      foreach ($errors as $error) {
        drupal_set_message("Error: {$error}.", 'error');
      }
    }
    $form_state->setRedirectUrl($range->toUrl('collection'));
  }

}
