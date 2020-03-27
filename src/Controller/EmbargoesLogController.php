<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

/**
 * Class EmbargoesLogController.
 */
class EmbargoesLogController extends ControllerBase {

  public function showRenderedLog() {
    $database = \Drupal::database();
    $result = $database->query('SELECT * FROM {embargoes_log} ORDER BY time DESC;');
    $formatted_log = [];
    foreach ($result as $record) {

      $formatted_time = date('c', $record->time);
      $node_title = \Drupal::entityTypeManager()->getStorage('node')->load($record->node)->get('title')->value;
      $username = \Drupal\user\Entity\User::load($record->user)->getUsername();

      $row = [
        'id' => $record->id,
        'time' => $formatted_time,
        'action' => $record->action,
        'node' => Markup::create("<a href='/node/{$record->node}'>$node_title</a>"),
        'user' =>  Markup::create("<a href='/user/{$record->user}'>$username</a>"),
        'embargo' => $record->embargo,
      ];
      array_push($formatted_log, $row);
    }

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
