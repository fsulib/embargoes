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
      '#default_value' => ( $embargo_id != 'add' ? $embargo->getEmbargoTypeAsInt() : FALSE ),
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
      '#default_value' => ( $embargo_id != 'add' ? $embargo->getExpirationTypeAsInt() : FALSE ),
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

    $form['exemptions']['exempt_ips'] = array(
      '#type' => 'select',
      '#title' => $this->t('Exempt IP ranges'),
      '#description' => $this->t('Select the name of a pre-configured IP range that is exempt from this specific embargo. IP ranges must be set up by an administrator.'),
      '#options' => \Drupal::service('embargoes.ips')->getIpRangesAsSelectOptions(),
      '#default_value' => ( $embargo_id != 'add' ? $embargo->getExemptIps() : FALSE ),
    );


    $form['exemptions']['exempt_users'] = array(
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#tags' => TRUE,
      '#title' => $this->t('Exempt users'),
      '#description' => $this->t('Enter the username of users that are exempt from this specific embargo. Use a comma to separate multiple exempt users.'),
      '#default_value' => ( $embargo_id != 'add' ? $embargo->getExemptUsersEntities() : FALSE ),
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
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $embargo_id = $form_state->getValue('embargo_id');
    if ($embargo_id == 'add') {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->create();
    }
    else {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
    }

    $embargo->setEmbargoType($form_state->getValue('embargo_type'));
    $embargo->setExpirationType($form_state->getValue('expiry_type'));
    $embargo->setExpirationDate($form_state->getValue('expiry_date'));
    $embargo->setExemptIps($form_state->getValue('exempt_ips'));
    $embargo->setExemptUsers($form_state->getValue('exempt_users'));
    $embargo->setEmbargoedNode($form_state->getValue('embargoed_node'));
    $embargo->save();

    $log_values['node'] = $embargo->getEmbargoedNode();
    $log_values['user'] = \Drupal::currentUser()->id();
    $log_values['embargo_id'] = $embargo->id();

    if ($embargo_id == 'add') {
      $log_values['action'] = 'created';
    }
    else {
      $log_values['action'] = 'updated';
    }

    \Drupal::messenger()->addMessage("Your embargo has been {$log_values['action']}.");
    \Drupal::service('embargoes.log')->logEmbargoEvent($log_values);
    $form_state->setRedirect('embargoes.node.embargoes', ['node' => $form_state->getValue('embargoed_node')]);
  }

}
