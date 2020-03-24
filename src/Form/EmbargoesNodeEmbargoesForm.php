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
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['embargo_type'] = array(
      '#type' => 'radios',
      '#title' => $this->t('Embargo type'),
      '#description' => $this->t('Select the type of embargo to be applied. "Files" will leave the node itself visible (including searches and indexing), only restricting access to the attached files. "Node" will suppress access of the node completely from users, searches and indexing.'),
      //'#default_value' => $config->get('expiry_type'), // TODO
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
      //'#default_value' => $config->get('expiry_type'), // TODO
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
      //'#default_value' => $config->get('expiry_date'), // TODO
      '#states' => [
        'visible' => [
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
      //'#default_value' => $config->get('expiry_type'), // TODO
      '#attributes' => [
        'name' => 'exempt_ips',
      ],
    ); 
    $form['exemptions']['exempt_users'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Exempt users'),
      '#description' => $this->t('Enter the username of users that are exempt from this specific embargo. Use a comma to separate multiple exempt users.'),
      //'#default_value' => $config->get('expiry_type'), // TODO
      '#attributes' => [
        'name' => 'exempt_users',
      ],
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
      dsm($key);
      dsm($value);
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
     #\Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
    \Drupal::messenger()->addMessage('Your embargo has been saved.');
  }

}
