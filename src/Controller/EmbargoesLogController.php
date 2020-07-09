<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Render\Markup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmbargoesLogController.
 */
class EmbargoesLogController extends ControllerBase {

  /**
   * Database connection.
   *
   * @var Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs an embargo log controller.
   */
  public function __construct(Connection $database) {
    $this->connection = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  /**
   * Creates markup for a log entry.
   *
   * @return array
   *   A renderable array of markup representing a log.
   */
  public function showRenderedLog() {
    $result = $this->connection
      ->select('embargoes_log', 'el')
      ->fields('el')
      ->orderBy('el.time', 'DESC')
      ->execute();

    $formatted_log = [];
    foreach ($result as $record) {
      $formatted_time = date('c', $record->time);
      $node_title = $this->entityTypeManager()->getStorage('node')->load($record->node)->get('title')->value;
      $user = $this->entityTypeManager()->getStorage('user')->load($record->uid);
      $username = $user ? $user->getUsername() : 'Missing User';
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
        'user' => Markup::create("<a href='/user/{$record->uid}'>$username</a>"),
      ];
      array_push($formatted_log, $row);
    }

    $pre_rendered_log = [
      '#type' => 'table',
      '#header' => [
        'Event ID',
        'Embargo ID',
        'Time',
        'Action',
        'Embargoed Node',
        'User Responsible',
      ],
      '#rows' => $formatted_log,
    ];

    return [
      '#type' => 'markup',
      '#markup' => render($pre_rendered_log),
    ];
  }

}
