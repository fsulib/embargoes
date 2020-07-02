<?php

namespace Drupal\embargoes;

use Drupal\Core\Database\Connection;

/**
 * Class EmbargoesLogService.
 */
class EmbargoesLogService implements EmbargoesLogServiceInterface {

  /**
   * @var \Drupal\Core\Database\Connection $database
   *
   * Database connection.
   */
  protected $database;

  /**
   * Constructs a new EmbargoesLogService object.
   */
  public function __construct(Connection $connection) {
    $this->database = $connection;
  }

  public function logEmbargoEvent($values) {
    $values['time'] = time();
    return $this->database->insert('embargoes_log')
      ->fields($values)
      ->execute();
  }

}
