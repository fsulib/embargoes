<?php

namespace Drupal\embargoes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining IP Range entities.
 */
interface EmbargoesIpRangeEntityInterface extends ConfigEntityInterface {

  public function id();
  public function label();

  public function getRange();
  public function setRange($range);

}
