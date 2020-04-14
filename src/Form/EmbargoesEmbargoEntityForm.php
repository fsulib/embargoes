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
    $embargo = $this->entity;

    $form['embargo_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Embargo type'),
      '#default_value' => $embargo->getEmbargoTypeAsInt(),
      '#options' => [
        '0' => t('Files'),
        '1' => t('Node'),
      ],
    );

    $form['expiration_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Expiration type'),
      '#default_value' => $embargo->getExpirationTypeAsInt(),
      '#options' => [
        '0' => t('Indefinite'),
        '1' => t('Scheduled'),
      ],
      '#attributes' => [
        'name' => 'expiry_type',
      ],
    );

    $form['expiration_date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#default_value' => $embargo->getExpirationDate(),
      '#states' => [
        'visible' => [
          ':input[name="expiry_type"]' => ['value' => '1'],
        ],
        'required' => [
          ':input[name="expiry_type"]' => ['value' => '1'],
        ],
      ],
    );

    $form['exempt_ips'] = array(
      '#type' => 'select',
      '#title' => $this->t('Exempt IP ranges'),
      '#options' => \Drupal::service('embargoes.ips')->getIpRangesAsSelectOptions(),
      '#default_value' => ( !is_null($embargo->getExemptIps()) ? $embargo->getExemptIps() : 'none' ),
    );

    $form['exempt_users'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Exempt users'),
      '#tags' => TRUE,
      '#default_value' => $embargo->getExemptUsersEntities(),
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
    );

    $form['additional_emails'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Additional Emails'),
      '#default_value' => $embargo->getAdditionalEmails(),
    );

    $form['embargoed_node'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Embargoed node'),
      '#default_value' => node_load($embargo->getEmbargoedNode()),
      '#required' => TRUE,
    );

    $form['notification_status'] = array(
      '#type' => 'select',
      '#title' => $this->t('Notification status'),
      '#default_value' => $embargo->getNotificationStatus(),
      '#options' => [
        'created' => 'Created',
        'updated' => 'Updated',
        'warned' => 'Warned',
        'expired' => 'Expired',
      ],
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $embargo = $this->entity;
    $embargo->setEmbargoType($form_state->getValue('embargo_type'));
    $embargo->setExpirationType($form_state->getValue('expiration_type'));
    $embargo->setExpirationDate($form_state->getValue('expiration_date'));
    $embargo->setExemptIps($form_state->getValue('exempt_ips'));
    $embargo->setExemptUsers($form_state->getValue('exempt_users'));
    $embargo->setAdditionalEmails($form_state->getValue('additional_emails'));
    $embargo->setEmbargoedNode($form_state->getValue('embargoed_node'));
    $embargo->setNotificationStatus($form_state->getValue('notification_status'));
    $status = $embargo->save();

    $log_values['node'] = $embargo->getEmbargoedNode();
    $log_values['user'] = \Drupal::currentUser()->id();
    $log_values['embargo_id'] = $embargo->id();

    if ($status == SAVED_NEW) {
        $log_values['action'] = 'created';
    }
    else {
        $log_values['action'] = 'updated';
    }

    \Drupal::messenger()->addMessage("Your embargo has been {$log_values['action']}.");
    \Drupal::service('embargoes.log')->logEmbargoEvent($log_values);
    $form_state->setRedirectUrl($embargo->toUrl('collection'));
  }

}
