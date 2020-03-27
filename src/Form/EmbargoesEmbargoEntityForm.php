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
        '0' => t('Indefinite'),
        '1' => t('Scheduled'),
      ],
    );

    $form['expiration_date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#default_value' => $embargoes_embargo_entity->getExpirationDate(),
    );

    $exempt_ip_range_options = \Drupal::service('embargoes.ips')->getIpRangesAsSelectOptions();
    
    $form['exempt_ips'] = array(
      '#type' => 'select',
      '#title' => $this->t('Exempt IP ranges'),
      '#options' => $exempt_ip_range_options, 
      '#default_value' => ( !is_null($embargoes_embargo_entity->getExemptIps()) ? $embargoes_embargo_entity->getExemptIps() : 'none' ),
    ); 

    $exempt_user_entities = [];
    foreach ($embargoes_embargo_entity->getExemptUsers() as $user) {
      $exempt_user_entities[] = \Drupal\user\Entity\User::load($user['target_id']);
    }

    $form['exempt_users'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Exempt users'),
      '#tags' => TRUE,
      '#default_value' => $exempt_user_entities,
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
    ); 

    $form['embargoed_node'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Embargoed node'),
      '#default_value' => node_load($embargoes_embargo_entity->getEmbargoedNode()),
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
    $embargoes_embargo_entity->setExemptIps($form_state->getValue('exempt_ips'));
    $embargoes_embargo_entity->setExemptUsers($form_state->getValue('exempt_users'));
    $embargoes_embargo_entity->setEmbargoedNode($form_state->getValue('embargoed_node'));
    $status = $embargoes_embargo_entity->save();

    $log_values['node'] = $embargoes_embargo_entity->getEmbargoedNode();
    $log_values['user'] = 1;
    $log_values['embargo_id'] = $embargoes_embargo_entity->id();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Embargo.', [
          '%label' => $embargoes_embargo_entity->label(),
        ]));
        $log_values['action'] = 'Created';
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Embargo.', [
          '%label' => $embargoes_embargo_entity->label(),
        ]));
        $log_values['action'] = 'Updated';
    }
    \Drupal::service('embargoes.log')->logEmbargoEvent($log_values);
    $form_state->setRedirectUrl($embargoes_embargo_entity->toUrl('collection'));
  }

}
