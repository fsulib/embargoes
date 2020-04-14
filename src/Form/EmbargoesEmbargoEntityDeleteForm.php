<?php

namespace Drupal\embargoes\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Builds the form to delete Embargo entities.
 */
class EmbargoesEmbargoEntityDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete embargo %id?', ['%id' => $this->entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $node = $this->entity->getEmbargoedNode();
    return new Url('embargoes.node.embargoes', ['node' => $node]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $log_values['node'] = $this->entity->getEmbargoedNode();
    $log_values['user'] = \Drupal::currentUser()->id();
    $log_values['embargo_id'] = $this->entity->id();
    $log_values['action'] = 'deleted';
    \Drupal::messenger()->addMessage("Your embargo has been {$log_values['action']}.");
    \Drupal::service('embargoes.log')->logEmbargoEvent($log_values);
    $this->entity->delete();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
