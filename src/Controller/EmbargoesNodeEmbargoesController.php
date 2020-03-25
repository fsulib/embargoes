<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

/**
 * Class EmbargoesLogController.
 */
class EmbargoesNodeEmbargoesController extends ControllerBase {

  public function showEmbargoes($node = NULL) {

    $query = \Drupal::entityQuery('embargoes_embargo_entity')
      ->condition('embargoed_node', $node);
    $embargo_ids = $query->execute();

    if (empty($embargo_ids)) {
      $markup['embargoes'] = [
        '#type' => 'markup',
        '#markup' => Markup::create('<p>There are no embargoes on this node.</p>'),
      ]; 
    }
    else {
      $rows = [];
      foreach ($embargo_ids as $embargo_id) {
        $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);

        if ($embargo->getExpirationType() == 1 ) {
          $expiry = 'Indefinite';
        } else {
          $expiry = $embargo->getExpirationDate();
        }
        $formatted_users = [];
        foreach ($embargo->getExemptUsers() as $user){
          $uid = $user['target_id'];
          $user_entity = \Drupal\user\Entity\User::load($uid);
          $user_name = $user_entity->getUserName();
          $formatted_users[] = "<a href='/user/{$uid}'>{$user_name}</a>";
        }
        $formatted_exempt_users_row = Markup::create(implode("<br>", $formatted_users));
        $ip_range_label = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps())->label();
        $row = [
          'type' => ($embargo->getEmbargoType() == 1 ? 'Node' : 'Files'),
          'expiry' => $expiry,
          'exempt_ips' => $ip_range_label,
          'exempt_users' => $formatted_exempt_users_row,
          'edit' => Markup::create("<a href='/node/{$node}/embargoes/{$embargo_id}'>Edit</a>"),
        ];
        array_push($rows, $row);
      }
      $markup['embargoes'] = [
        '#type' => 'table',
        '#header' => ['Type', 'Expiry', 'Exempt IP Range', 'Exempt Users', 'Edit'],
        '#rows' => $rows,
      ];
    }

    $markup['add'] = [
      '#type' => 'markup',
      '#markup' => Markup::create("<p><a href='/node/{$node}/embargoes/create'>Add Embargo</a></p>"),
    ];

    return [
      '#type' => 'markup',
      '#markup' => render($markup),
    ];
  }

}
