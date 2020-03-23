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

    $embargoes_ip_range_entity = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $embargoes_ip_range_entity->label(),
      '#description' => $this->t("Label for the IP Range."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $embargoes_ip_range_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\embargoes\Entity\EmbargoesIpRangeEntity::load',
      ],
      '#disabled' => !$embargoes_ip_range_entity->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $embargoes_ip_range_entity = $this->entity;
    $status = $embargoes_ip_range_entity->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label IP Range.', [
          '%label' => $embargoes_ip_range_entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label IP Range.', [
          '%label' => $embargoes_ip_range_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($embargoes_ip_range_entity->toUrl('collection'));
  }

}
