<?php

namespace Drupal\embargoes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Embargo entities.
 */
interface EmbargoesEmbargoEntityInterface extends ConfigEntityInterface {

  public function getEmbargoType();
  public function setEmbargoType($type);


  public function getExpirationType();
  public function setExpirationType($type);

  public function getExpirationDate();
  public function setExpirationDate($date);

}
