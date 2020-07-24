<?php

namespace Drupal\embargoes\Form;

use Drupal\embargoes\EmbargoesIpRangesServiceInterface;
use Drupal\embargoes\EmbargoesLogServiceInterface;
use Drupal\node\NodeInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmbargoesNodeEmbargoesForm.
 */
class EmbargoesNodeEmbargoesForm extends FormBase {

  /**
   * An entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

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
   * A UUID generator service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * Constructor for the node embargo form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   An entity type manager.
   * @param \Drupal\embargoes\EmbargoesIpRangesServiceInterface $ip_ranges
   *   An embargoes IP ranges manager.
   * @param \Drupal\embargoes\EmbargoesLogServiceInterface $embargoes_log
   *   An embargoes logging service.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_generator
   *   A UUID generator.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, EmbargoesIpRangesServiceInterface $ip_ranges, EmbargoesLogServiceInterface $embargoes_log, UuidInterface $uuid_generator) {
    $this->entityManager = $entity_manager;
    $this->ipRanges = $ip_ranges;
    $this->embargoesLog = $embargoes_log;
    $this->uuidGenerator = $uuid_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('embargoes.ips'),
      $container->get('embargoes.log'),
      $container->get('uuid'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'embargoes_node_embargoes';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL, $embargo_id = NULL) {

    if ($embargo_id != "add") {
      $embargo = $this->entityManager->getStorage('embargoes_embargo_entity')->load($embargo_id);
    }

    $form['embargo_id'] = [
      '#type' => 'hidden',
      '#value' => $embargo_id,
    ];

    $form['embargoed_node'] = [
      '#type' => 'hidden',
      '#value' => $node->id(),
    ];

    $form['embargo_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Embargo type'),
      '#description' => $this->t('Select the type of embargo to be applied. "Files" will leave the node itself visible (including searches and indexing), only restricting access to the attached files. "Node" will suppress access of the node completely from users, searches and indexing.'),
      '#default_value' => ($embargo_id != 'add' ? $embargo->getEmbargoTypeAsInt() : FALSE),
      '#required' => TRUE,
      '#options' => [
        '0' => $this->t('Files'),
        '1' => $this->t('Node'),
      ],
      '#attributes' => [
        'name' => 'embargo_type',
      ],
    ];

    $form['expiry'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Expiration'),
    ];

    $form['expiry']['expiry_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Expiration type'),
      '#default_value' => ($embargo_id != 'add' ? $embargo->getExpirationTypeAsInt() : FALSE),
      '#required' => TRUE,
      '#options' => [
        '0' => $this->t('Indefinite'),
        '1' => $this->t('Scheduled'),
      ],
      '#attributes' => [
        'name' => 'expiry_type',
      ],
    ];

    $form['expiry']['expiry_date'] = [
      '#type' => 'date',
      '#title' => $this->t('Expiration date'),
      '#description' => $this->t('Schedule the date on which the embargo will expire'),
      '#default_value' => ($embargo_id != 'add' ? $embargo->getExpirationDate() : FALSE),
      '#states' => [
        'visible' => [
          ':input[name="expiry_type"]' => ['value' => '1'],
        ],
        'required' => [
          ':input[name="expiry_type"]' => ['value' => '1'],
        ],
      ],
    ];

    $form['exemptions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Exemptions'),
    ];

    $form['exemptions']['exempt_ips'] = [
      '#type' => 'select',
      '#title' => $this->t('Exempt IP ranges'),
      '#description' => $this->t('Select the name of a pre-configured IP range that is exempt from this specific embargo. IP ranges must be set up by an administrator.'),
      '#options' => $this->ipRanges->getIpRangesAsSelectOptions(),
      '#default_value' => ($embargo_id != 'add' ? (is_null($embargo->getExemptIps()) ? NULL : $embargo->getExemptIps()) : NULL),
    ];

    $form['exemptions']['exempt_users'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#tags' => TRUE,
      '#title' => $this->t('Exempt users'),
      '#description' => $this->t('Enter the username of users that are exempt from this specific embargo. Use a comma to separate multiple exempt users.'),
      '#default_value' => ($embargo_id != 'add' ? $embargo->getExemptUsersEntities() : FALSE),
    ];

    $form['additional_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Additional Emails'),
      '#description' => $this->t('A comma-separated list of emails addresses that should recieve notifications regarding this embargo.'),
      '#default_value' => ($embargo_id != 'add' ? implode(',', $embargo->getAdditionalEmails()) : ''),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $embargo_id = $form_state->getValue('embargo_id');
    if ($embargo_id == 'add') {
      $embargo = $this->entityManager->getStorage('embargoes_embargo_entity')->create([
        'id' => sha1($this->uuidGenerator->generate()),
      ]);
    }
    else {
      $embargo = $this->entityManager->getStorage('embargoes_embargo_entity')->load($embargo_id);
    }

    $embargo->setEmbargoType($form_state->getValue('embargo_type'));
    $embargo->setExpirationType($form_state->getValue('expiry_type'));
    $embargo->setExpirationDate($form_state->getValue('expiry_date'));
    $embargo->setExemptIps($form_state->getValue('exempt_ips'));
    $embargo->setExemptUsers($form_state->getValue('exempt_users'));
    $embargo->setAdditionalEmails($form_state->getValue('additional_emails'));
    $embargo->setEmbargoedNode($form_state->getValue('embargoed_node'));
    $embargo->save();

    $log_values['node'] = $embargo->getEmbargoedNode();
    $log_values['uid'] = $this->currentUser()->id();
    $log_values['embargo'] = $embargo->id();

    if ($embargo_id == 'add') {
      $log_values['action'] = 'created';
    }
    else {
      $log_values['action'] = 'updated';
    }

    $this->messenger()->addMessage("Your embargo has been {$log_values['action']}.");
    $this->embargoesLog->logEmbargoEvent($log_values);
    $form_state->setRedirect('embargoes.node.embargoes', ['node' => $form_state->getValue('embargoed_node')]);
  }

}
