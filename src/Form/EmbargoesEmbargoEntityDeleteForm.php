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
    $this->entity->delete();

    $this->messenger()->addMessage(
      $this->t('Embargo @id has been deleted.', [
        '@id' => $this->entity->id(),
      ])
    );


    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
