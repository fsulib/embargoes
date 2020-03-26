<?php

namespace Drupal\embargoes\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmbargoesNodeEmbargoesForm.
 */
class EmbargoesNodeEmbargoesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'embargoes_node_embargoes';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL, $embargo_id = NULL) {


    if ($embargo_id != "add") {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
    }

    $form['embargo_id'] = array(
      '#type' => 'hidden',
      '#value' => $embargo_id,
    );

    $form['embargoed_node'] = array(
      '#type' => 'hidden',
      '#value' => $node,
    );

    $form['embargo_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Embargo type'),
      '#description' => $this->t('Select the type of embargo to be applied. "Files" will leave the node itself visible (including searches and indexing), only restricting access to the attached files. "Node" will suppress access of the node completely from users, searches and indexing.'),
      '#default_value' => ( $embargo_id != 'add' ? $embargo->getEmbargoType() : 0 ), 
      '#required' => TRUE,
      '#options' => [ 
        '0' => t('Files'),
        '1' => t('Node'),
      ],
      '#attributes' => [
        'name' => 'embargo_type',
      ],
    ); 

    $form['expiry'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Expiration'),
    );

    $form['expiry']['expiry_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Expiration type'),
      '#default_value' => ( $embargo_id != 'add' ? intval($embargo->getExpirationType()) : 0 ), 
      '#required' => TRUE,
      '#options' => [ 
        '0' => t('Indefinite'),
        '1' => t('Scheduled'),
      ],
      '#attributes' => [
        'name' => 'expiry_type',
      ],
    ); 
    $form['expiry']['expiry_date'] = array(
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#description' => $this->t('Schedule the date on which the embargo will expire'),
      '#default_value' => ( $embargo_id != 'add' ? $embargo->getExpirationDate() : FALSE ), 
      '#states' => [
        'visible' => [
          ':input[name="expiry_type"]' => ['value' => '1'],
        ],
        'required' => [
          ':input[name="expiry_type"]' => ['value' => '1'],
        ],
      ],
    );

    $form['exemptions'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Exemptions'),
    );

    $ip_ranges = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->loadMultiple();
    $ip_range_options = [];
    $ip_range_options['none'] = 'None';
    foreach ($ip_ranges as $ip_range) {
      $ip_range_options[$ip_range->id()] = $ip_range->label();
    }
   
    

    $form['exemptions']['exempt_ips'] = array(
      '#type' => 'select',
      '#title' => $this->t('Exempt IP ranges'),
      '#description' => $this->t('Select the name of a pre-configured IP range that is exempt from this specific embargo. IP ranges must be set up by an administrator.'),
      '#options' => $ip_range_options,
      '#default_value' => ( $embargo_id != 'add' ? $embargo->getExemptIps() : FALSE ), 
    ); 


    if ($embargo_id != 'add') {
      $exempt_user_entities = [];
      foreach ($embargo->getExemptUsers() as $user) {
        $exempt_user_entities[] = \Drupal\user\Entity\User::load($user['target_id']);
      }
    }
    else {
      $exempt_user_entities = FALSE;
    }
 

    $form['exemptions']['exempt_users'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#tags' => TRUE,
      '#title' => $this->t('Exempt users'),
      '#description' => $this->t('Enter the username of users that are exempt from this specific embargo. Use a comma to separate multiple exempt users.'),
      '#default_value' => $exempt_user_entities, 
    ); 

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $embargo_id = $form_state->getValue('embargo_id');
    if ($embargo_id == 'add') {
      $uuid = \Drupal::service('uuid')->generate();
      $formatted_uuid = str_replace('-', '_', $uuid);
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->create([
        'embargo_type' => $form_state->getValue('embargo_type'),
        'expiry_type' => $form_state->getValue('expiry_type'),
        'expiry_date' => $form_state->getValue('expiry_date'),
        'exempt_ips' => $form_state->getValue('exempt_ips'),
        'exempt_users' => $form_state->getValue('exempt_users'),
        'embargoed_node' => $form_state->getValue('embargoed_node'),
      ]);
      $embargo->save();
      \Drupal::messenger()->addMessage('Your embargo has been saved.');
    }
    else {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
      $embargo->setEmbargoType($form_state->getValue('embargo_type'));
      $embargo->setExpirationType($form_state->getValue('embargo_type'));
      $embargo->setExpirationDate($form_state->getValue('embargo_date'));
      $embargo->setExemptIps($form_state->getValue('exempt_ips'));
      $embargo->setExemptUsers($form_state->getValue('exempt_users'));
      $embargo->setEmbargoedNode($form_state->getValue('embargoed_node'));
      $status = $embargo->save();
      \Drupal::messenger()->addMessage('Your embargo has been updated.');
    }

  }

}
