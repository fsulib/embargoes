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
    $time = time();
    $database = \Drupal::database();
    $result = $database->query("INSERT INTO {embargoes_log} (time, action, node, user, embargo) VALUES ('{$time}', '{$values['action']}', '{$values['node']}', '{$values['user']}','{$values['embargo_id']}');");
    return $result;
  }

}
