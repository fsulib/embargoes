<?php

namespace Drupal\embargoes\Entity;

use Drupal\user\Entity\User;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Embargo entity.
 *
 * @ConfigEntityType(
 *   id = "embargoes_embargo_entity",
 *   label = @Translation("Embargo"),
 *   handlers = { *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\embargoes\Controller\EmbargoesEmbargoEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\embargoes\Form\EmbargoesEmbargoEntityForm",
 *       "edit" = "Drupal\embargoes\Form\EmbargoesEmbargoEntityForm",
 *       "delete" = "Drupal\embargoes\Form\EmbargoesEmbargoEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\embargoes\EmbargoesEmbargoEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "embargoes_embargo_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/content/embargoes/settings/embargoes/{embargoes_embargo_entity}",
 *     "add-form" = "/admin/config/content/embargoes/settings/embargoes/add",
 *     "edit-form" = "/admin/config/content/embargoes/settings/embargoes/{embargoes_embargo_entity}/edit",
 *     "delete-form" = "/admin/config/content/embargoes/settings/embargoes/{embargoes_embargo_entity}/delete",
 *     "collection" = "/admin/config/content/embargoes/settings/embargoes"
 *   }
 * )
 */
class EmbargoesEmbargoEntity extends ConfigEntityBase implements EmbargoesEmbargoEntityInterface {

  /**
   * The Embargo ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The type of embargo.
   *
   * @var bool
   */
  protected $embargo_type;

  /**
   * The type of expiration.
   *
   * @var bool
   */
  protected $expiration_type;

  /**
   * The date of expiration for a scheduled embargo.
   *
   * @var string
   */
  protected $expiration_date;

  /**
   * The ID of a configured IP exemption range, or NULL.
   *
   * @var string|null
   */
  protected $exempt_ips;

  /**
   * An array of user UIDs exempt from the embargo.
   *
   * @var int[]
   */
  protected $exempt_users = [];

  /**
   * An array of email addresses to be notified in regards to the embargo.
   *
   * @var string[]
   */
  protected $additional_emails = [];

  /**
   * The ID of the node this embargo applies to.
   *
   * @var int
   */
  protected $embargoed_node;

  /**
   * The current notification status of the embargo.
   *
   * Either 'created', 'updated', 'warned', or 'expired'.
   *
   * @var string
   */
  protected $notification_status;

  /**
   * {@inheritdoc}
   */
  public function save() {
    parent::save();
    drupal_flush_all_caches();
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    parent::delete();
    drupal_flush_all_caches();
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbargoType() {
    return $this->get('embargo_type');
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbargoTypeAsInt() {
    return intval($this->get('embargo_type'));
  }

  /**
   * {@inheritdoc}
   */
  public function setEmbargoType($type) {
    $this->set('embargo_type', $type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExpirationType() {
    return $this->get('expiration_type');
  }

  /**
   * {@inheritdoc}
   */
  public function getExpirationTypeAsInt() {
    return intval($this->get('expiration_type'));
  }

  /**
   * {@inheritdoc}
   */
  public function setExpirationType($type) {
    $this->set('expiration_type', $type);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExpirationDate() {
    return $this->get('expiration_date');
  }

  /**
   * {@inheritdoc}
   */
  public function setExpirationDate($date) {
    $this->set('expiration_date', $date);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExemptIps() {
    return $this->get('exempt_ips');
  }

  /**
   * {@inheritdoc}
   */
  public function setExemptIps($range) {
    $this->set('exempt_ips', $range);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExemptUsers() {
    return $this->get('exempt_users');
  }

  /**
   * {@inheritdoc}
   */
  public function getExemptUsersEntities() {
    $exempt_user_entities = [];
    foreach ($this->getExemptUsers() as $user) {
      $exempt_user_entities[] = User::load($user['target_id']);
    }
    return $exempt_user_entities;
  }

  /**
   * {@inheritdoc}
   */
  public function setExemptUsers($users) {
    if (!$users) {
      $users = [];
    }
    $this->set('exempt_users', $users);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAdditionalEmails() {
    return $this->get('additional_emails');
  }

  /**
   * {@inheritdoc}
   */
  public function setAdditionalEmails($emails) {
    $emails = empty($emails) ? [] : array_map('trim', explode(',', trim($emails)));
    $this->set('additional_emails', $emails);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmbargoedNode() {
    return $this->get('embargoed_node');
  }

  /**
   * {@inheritdoc}
   */
  public function setEmbargoedNode($node) {
    $this->set('embargoed_node', $node);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNotificationStatus() {
    return $this->get('notification_status');
  }

  /**
   * {@inheritdoc}
   */
  public function getValidNotificationStatuses() {
    return [
      self::STATUS_CREATED,
      self::STATUS_UPDATED,
      self::STATUS_WARNED,
      self::STATUS_EXPIRED,
      self::STATUS_DELETED,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setNotificationStatus($status) {
    $valid_statuses = $this->getValidNotificationStatuses();
    if (!in_array($status, $valid_statuses)) {
      throw new \InvalidArgumentException('The notification status must be one of ' . implode(', ', $valid_statuses));
    }
    $this->set('notification_status', $status);
    return $this;
  }

}
