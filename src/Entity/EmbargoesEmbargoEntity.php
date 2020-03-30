<?php

namespace Drupal\embargoes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Embargo entity.
 *
 * @ConfigEntityType(
 *   id = "embargoes_embargo_entity",
 *   label = @Translation("Embargo"),
 *   handlers = { *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\embargoes\EmbargoesEmbargoEntityListBuilder",
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

  protected $embargo_type;

  protected $expiration_type;

  protected $expiration_date;

  protected $exempt_ips;

  protected $exempt_users;

  protected $additional_emails;

  protected $embargoed_node;

  public function __construct(array $values, $entity_type) {
    $uuid = \Drupal::service('uuid')->generate();
    $checksummed_uuid = sha1($uuid);
    $this->uuid = $uuid;
    $this->id = $checksummed_uuid;
    parent::__construct($values, $entity_type);
  }

  public function getEmbargoType() {
    return $this->get('embargo_type');
  }

  public function getEmbargoTypeAsInt() {
    return intval($this->get('embargo_type'));
  }

  public function setEmbargoType($type){
    $this->set('embargo_type', $type);
    return $this;
  }

  public function getExpirationType() {
    return $this->get('expiration_type');
  }

  public function getExpirationTypeAsInt() {
    return intval($this->get('expiration_type'));
  }

  public function setExpirationType($type){
    $this->set('expiration_type', $type);
    return $this;
  }

  public function getExpirationDate() {
    return $this->get('expiration_date');
  }

  public function setExpirationDate($date){
    $this->set('expiration_date', $date);
    return $this;
  }

  public function getExemptIps() {
    return $this->get('exempt_ips');
  }

  public function setExemptIps($range){
    $this->set('exempt_ips', $range);
    return $this;
  }

  public function getExemptUsers() {
    return $this->get('exempt_users');
  }

  public function getExemptUsersEntities() {
    $exempt_user_entities = [];
    foreach ($this->getExemptUsers() as $user) {
      $exempt_user_entities[] = \Drupal\user\Entity\User::load($user['target_id']);
    }
    return $exempt_user_entities;
  }

  public function setExemptUsers($users){
    $this->set('exempt_users', $users);
    return $this;
  }

  public function getAdditionalEmails() {
    return $this->get('additional_emails');
  }

  public function setAdditionalEmails($emails){
    $this->set('additional_emails', $emails);
    return $this;
  }

  public function getEmbargoedNode() {
    return $this->get('embargoed_node');
  }

  public function setEmbargoedNode($node){
    $this->set('embargoed_node', $node);
    return $this;
  }

}
