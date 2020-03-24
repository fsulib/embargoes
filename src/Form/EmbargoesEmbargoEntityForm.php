<?php

namespace Drupal\embargoes\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmbargoesEmbargoEntityForm.
 */
class EmbargoesEmbargoEntityForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $embargoes_embargo_entity = $this->entity;

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $embargoes_embargo_entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\embargoes\Entity\EmbargoesEmbargoEntity::load',
      ],
      '#disabled' => !$embargoes_embargo_entity->isNew(),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $embargoes_embargo_entity->label(),
      '#description' => $this->t("Label for the Embargo."),
      '#required' => TRUE,
    ];

    $form['embargo_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Embargo type'),
      '#default_value' => ($embargoes_embargo_entity->getEmbargoType() == 1 ? 1 : 0),
      '#options' => [
        '0' => t('Files'),
        '1' => t('Node'),
      ],
    );

    $form['expiration_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Expiration type'),
      '#default_value' => ($embargoes_embargo_entity->getExpirationType() == 1 ? 1 : 0),
      '#options' => [
        '0' => t('Scheduled'),
        '1' => t('Indefinite'),
      ],
    );

    $form['expiration_date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#default_value' => $embargoes_embargo_entity->getExpirationDate(),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $embargoes_embargo_entity = $this->entity;
    $embargoes_embargo_entity->setEmbargoType($form_state->getValue('embargo_type'));
    $embargoes_embargo_entity->setExpirationType($form_state->getValue('expiration_type'));
    $embargoes_embargo_entity->setExpirationDate($form_state->getValue('expiration_date'));

    $status = $embargoes_embargo_entity->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Embargo.', [
          '%label' => $embargoes_embargo_entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Embargo.', [
          '%label' => $embargoes_embargo_entity->label(),
        ]));
    }
    $form_state->setRedirectUrl($embargoes_embargo_entity->toUrl('collection'));
  }

}
