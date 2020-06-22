<?php

namespace Drupal\embargoes;

/**
 * Class EmbargoesLogService.
 */
class EmbargoesLogService implements EmbargoesLogServiceInterface {

  /**
   * Constructs a new EmbargoesLogService object.
   */
  public function __construct() {

  }

  public function logEmbargoEvent($values) {
    $conn = \Drupal::database()->getConnection();
    $values['time'] = time();
    return $conn->insert('embargoes_log')
      ->fields($values)
      ->execute();
  }

}
