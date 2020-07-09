<?php

namespace Drupal\embargoes\Form;

use Drupal\embargoes\EmbargoesLogServiceInterface;
use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to delete Embargo entities.
 */
class EmbargoesEmbargoEntityDeleteForm extends EntityConfirmFormBase {

  /**
   * Embargoes logging service.
   *
   * @var \Drupal\embargoes\EmbargoesLogServiceInterface
   */
  protected $logger;

  /**
   * Messaging interface.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Creates the delete form.
   *
   * @param \Drupal\embargoes\EmbargoesLogServiceInterface $log_service
   *   An embargoes logging service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   Messaging interface.
   */
  public function __construct(EmbargoesLogServiceInterface $log_service, MessengerInterface $messenger) {
    $this->logger = $log_service;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('embargoes.log'),
      $container->get('messenger'));
  }

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
    $log_values = [
      'node' => $this->entity->getEmbargoedNode(),
      'uid' => $this->currentUser()->id(),
      'embargo' => $this->entity->id(),
      'action' => 'deleted',
    ];
    $this->messenger()->addMessage($this->t("Your embargo has been deleted."));
    $this->logger->logEmbargoEvent($log_values);
    $this->entity->delete();
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
