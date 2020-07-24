<?php

namespace Drupal\embargoes;

use Drupal\Core\Database\Connection;

/**
 * Class EmbargoesLogService.
 */
class EmbargoesLogService implements EmbargoesLogServiceInterface {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new EmbargoesLogService object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   A database connection object.
   */
  public function __construct(Connection $connection) {
    $this->database = $connection;
  }

  /**
   * Logs an embargo event in the embargoes_log table.
   *
   * @param array $values
   *   An associative array pairing columns in the new log with the values that
   *   should be placed in that new log. The 'time' parameter will be generated
   *   and is overwritten if provided. Other than that the following must be
   *   provided:
   *   - 'action': Either 'created' or 'updated'.
   *   - 'node': The node ID this embargo event applied to.
   *   - 'embargo': The ID of the embargo associated with this event.
   *   - 'uid': The ID of the user that instantiated this event.
   */
  public function logEmbargoEvent(array $values) {
    $values['time'] = time();
    return $this->database->insert('embargoes_log')
      ->fields($values)
      ->execute();
  }

}
