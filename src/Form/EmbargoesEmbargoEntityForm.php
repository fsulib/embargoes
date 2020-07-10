<?php

namespace Drupal\embargoes\Form;

use Drupal\embargoes\EmbargoesIpRangesServiceInterface;
use Drupal\embargoes\EmbargoesLogServiceInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmbargoesEmbargoEntityForm.
 */
class EmbargoesEmbargoEntityForm extends EntityForm {

  /**
   * An embargoes IP ranges manager.
   *
   * @var \Drupal\embargoes\EmbargoesIpRangesServiceInterface
   */
  protected $ipRanges;

  /**
   * An embargoes logging service.
   *
   * @var \Drupal\embargoes\EmbargoesLogServiceInterface
   */
  protected $embargoesLog;

  /**
   * UUID interface.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * Constructor for the node embargo form.
   *
   * @param \Drupal\embargoes\EmbargoesIpRangesServiceInterface $ip_ranges
   *   An embargoes IP ranges manager.
   * @param \Drupal\embargoes\EmbargoesLogServiceInterface $embargoes_log
   *   An embargoes logging service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_generator
   *   A UUID generator.
   */
  public function __construct(EmbargoesIpRangesServiceInterface $ip_ranges, EmbargoesLogServiceInterface $embargoes_log, UuidInterface $uuid_generator) {
    $this->ipRanges = $ip_ranges;
    $this->embargoesLog = $embargoes_log;
    $this->uuidGenerator = $uuid_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('embargoes.ips'),
      $container->get('embargoes.log'),
      $container->get('uuid'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $embargo = $this->entity;

    $id = $this->entity->id();
    $form['id'] = [
      '#type' => 'value',
      '#value' => isset($id) ? $id : sha1($this->uuidGenerator->generate()),
    ];

    $form['embargo_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Embargo type'),
      '#default_value' => $embargo->getEmbargoTypeAsInt(),
      '#options' => [
        '0' => $this->t('Files'),
        '1' => $this->t('Node'),
      ],
    ];

    $form['expiration_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Expiration type'),
      '#default_value' => $embargo->getExpirationTypeAsInt(),
      '#options' => [
        '0' => $this->t('Indefinite'),
        '1' => $this->t('Scheduled'),
      ],
      '#attributes' => [
        'name' => 'expiry_type',
      ],
    ];

    $form['expiration_date'] = [
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
    ];

    $form['exempt_ips'] = [
      '#type' => 'select',
      '#title' => $this->t('Exempt IP ranges'),
      '#options' => $this->ipRanges->getIpRangesAsSelectOptions(),
      '#default_value' => (!is_null($embargo->getExemptIps()) ? $embargo->getExemptIps() : NULL),
    ];

    $form['exempt_users'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Exempt users'),
      '#tags' => TRUE,
      '#default_value' => $embargo->getExemptUsersEntities(),
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
    ];

    $form['additional_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Additional Emails'),
      '#default_value' => implode(',', $embargo->getAdditionalEmails()),
    ];

    $embargoed_node = $embargo->getEmbargoedNode();
    if ($embargoed_node) {
      $embargoed_node = $this->entityTypeManager->getStorage('node')->load($embargoed_node);
    }
    $form['embargoed_node'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#title' => $this->t('Embargoed node'),
      '#maxlength' => 255,
      '#default_value' => $embargoed_node ? $embargoed_node : [],
      '#required' => TRUE,
    ];

    $form['notification_status'] = [
      '#type' => 'select',
      '#title' => $this->t('Notification status'),
      '#default_value' => $embargo->getNotificationStatus(),
      '#options' => [
        'created' => $this->t('Created'),
        'updated' => $this->t('Updated'),
        'warned' => $this->t('Warned'),
        'expired' => $this->t('Expired'),
      ],
      '#required' => TRUE,
    ];

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
    $log_values['uid'] = $this->currentUser()->id();
    $log_values['embargo'] = $embargo->id();

    if ($status == SAVED_NEW) {
      $log_values['action'] = 'created';
    }
    else {
      $log_values['action'] = 'updated';
    }

    $this->messenger()->addMessage("Your embargo has been {$log_values['action']}.");
    $this->embargoesLog->logEmbargoEvent($log_values);
    $form_state->setRedirectUrl($embargo->toUrl('collection'));
  }

}
