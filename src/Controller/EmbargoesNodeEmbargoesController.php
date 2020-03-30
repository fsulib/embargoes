<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

/**
 * Class EmbargoesLogController.
 */
class EmbargoesNodeEmbargoesController extends ControllerBase {

  public function showEmbargoes($node = NULL) {

    $embargo_ids = \Drupal::service('embargoes.embargoes')->getAllEmbargoesByNode($node);
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

        if ($embargo->getExpirationType() == 0 ) {
          $expiry = 'Indefinite';
        } else {
          $expiry = $embargo->getExpirationDate();
        }

        $formatted_users = [];
        $exempt_users = $embargo->getExemptUsers();
        if (empty($exempt_users)) {
          $formatted_users[] = "None";
        }
        else {
          foreach ($embargo->getExemptUsers() as $user){
            $uid = $user['target_id'];
            $user_entity = \Drupal\user\Entity\User::load($uid);
            $user_name = $user_entity->getUserName();
            $formatted_users[] = "<a href='/user/{$uid}'>{$user_name}</a>";
          }
        }
        $formatted_exempt_users_row = Markup::create(implode("<br>", $formatted_users));

        if ($embargo->getExemptIps() != 'none') {
          $ip_range = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps());
          $ip_range_label = $ip_range->label();
          $ip_range_formatted = Markup::create("<a href='/admin/structure/embargoes_ip_range_entity/{$embargo->getExemptIps()}/edit'>{$ip_range_label}</a>");
        }
        else {
          $ip_range_formatted = "None";
        }

        $formatted_emails = Markup::create(str_replace(',', '<br>', str_replace(' ', '', $embargo->getAdditionalEmails())));

        $row = [
          'type' => ($embargo->getEmbargoType() == 1 ? 'Node' : 'Files'),
          'expiry' => $expiry,
          'exempt_ips' => $ip_range_formatted,
          'exempt_users' => $formatted_exempt_users_row,
          'additional_emails' => $formatted_emails,
          'edit' => Markup::create("<a href='/node/{$node}/embargoes/{$embargo_id}'>Edit</a><br><a href='/admin/config/content/embargoes/settings/embargoes/{$embargo_id}/delete'>Delete</a>"),
        ];
        array_push($rows, $row);
      }

      $markup['embargoes'] = [
        '#type' => 'table',
        '#header' => ['Type', 'Expiration Date', 'Exempt IP Range', 'Exempt Users', 'Additional Emails', 'Edit'],
        '#rows' => $rows,
      ];
    }

    $markup['add'] = [
      '#type' => 'markup',
      '#markup' => Markup::create("<p><a href='/node/{$node}/embargoes/add'>Add Embargo</a></p>"),
    ];

    return [
      '#type' => 'markup',
      '#markup' => render($markup),
    ];
  }

}
