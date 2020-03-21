<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class EmbargoesLogController.
 */
class EmbargoesLogController extends ControllerBase {

  public function logEmbargoEvent($data) {
    $database = \Drupal::database();
    $result = $database->query("INSERT INTO {embargoes_log} (time, action, node, user, embargo) VALUES ('{$data['time']}', '{$data['action']}', '{$data['node']}', '{$data['user']}','{$data['embargo']}');");
  }

  public function getRawDatabaseLog() {
    $database = \Drupal::database();
    $result = $database->query('SELECT * FROM {embargoes_log} ORDER BY time DESC;');
    $raw_db_log = [];
    foreach ($result as $record) {
      $row = [
        'id' => $record->id,
        'time' => $record->time,
        'action' => $record->action,
        'node' => $record->node,
        'user' => $record->user,
        'embargo' => $record->embargo,
      ];
      array_push($raw_db_log, $row);
    }
 
    return $raw_db_log;
  }

  public function getFormattedDatabaseLog() {
    $log = $this->getRawDatabaseLog();
    $formatted_log = $log;

    return $formatted_log;
  }

  public function showRenderedLog() {
    $formatted_log = $this->getFormattedDatabaseLog();
    $pre_rendered_log = [
      '#type' => 'table',
      '#header' => ['ID', 'Time', 'Action', 'Node', 'User', 'Embargo'],
      '#rows' => $formatted_log,
    ];

    return [
      '#type' => 'markup',
      '#markup' => render($pre_rendered_log),
    ];
  }

}
