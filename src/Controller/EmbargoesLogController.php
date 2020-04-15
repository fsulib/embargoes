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
      if ($record->action == "deleted") {
        $embargo_formatted = Markup::create("<span style='text-decoration:line-through;'>{$record->embargo}</span>");
      }
      else {
        $embargo_formatted = Markup::create("<a href='/admin/config/content/embargoes/settings/embargoes/{$record->embargo}/edit'>{$record->embargo}</a>");
      }

      $row = [
        'id' => $record->id,
        'embargo' => $embargo_formatted,
        'time' => $formatted_time,
        'action' => ucfirst($record->action),
        'node' => Markup::create("<a href='/node/{$record->node}'>$node_title</a>"),
        'user' =>  Markup::create("<a href='/user/{$record->user}'>$username</a>"),
      ];
      array_push($formatted_log, $row);
    }

    $pre_rendered_log = [
      '#type' => 'table',
      '#header' => ['Event ID', 'Embargo ID', 'Time', 'Action', 'Embargoed Node', 'User Responsible'],
      '#rows' => $formatted_log,
    ];

    return [
      '#type' => 'markup',
      '#markup' => render($pre_rendered_log),
    ];
  }

}
