<?php

namespace Drupal\embargoes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Embargo entities.
 */
interface EmbargoesEmbargoEntityInterface extends ConfigEntityInterface {

  public function getEmbargoType();
  public function getEmbargoTypeAsInt();
  public function setEmbargoType($type);

  public function getExpirationType();
  public function getExpirationTypeAsInt();
  public function setExpirationType($type);

  public function getExpirationDate();
  public function setExpirationDate($date);

  public function getExemptIps();
  public function setExemptIps($range);

  public function getExemptUsers();
  public function getExemptUsersEntities();
  public function setExemptUsers($user);

  public function getEmbargoedNode();
  public function setEmbargoedNode($node);

  public function getAdditionalEmails();
  public function setAdditionalEmails($emails);

  public function getNotificationStatus();
  public function setNotificationStatus($status);

}
