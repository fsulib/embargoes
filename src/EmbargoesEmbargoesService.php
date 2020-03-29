<?php

namespace Drupal\embargoes;

/**
 * Class EmbargoesEmbargoesService.
 */
class EmbargoesEmbargoesService implements EmbargoesEmbargoesServiceInterface {

  /**
   * Constructs a new EmbargoesEmbargoesService object.
   */
  public function __construct() {
  }

  public function getAllEmbargoesByNode($nid) {
    $query = \Drupal::entityQuery('embargoes_embargo_entity')
      ->condition('embargoed_node', $nid);
    $embargoes = $query->execute();
    return $embargoes;
  }

  public function getCurrentEmbargoesByNode($nid) {
    $current_embargoes = [];
    $embargoes = \Drupal::service('embargoes.embargoes')->getAllEmbargoesByNode($nid);
    foreach ($embargoes as $embargo_id) {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
      if ($embargo->getExpirationTypeAsInt() == 0) {
        $current_embargoes[$embargo_id] = $embargo_id;
      }
      else {
        $now = time();
        $expiry = strtotime($embargo->getExpirationDate());
        if ($expiry > $now) {
          $current_embargoes[$embargo_id] = $embargo_id;
        }
      }
    }
    return $current_embargoes;
  }

  public function getActiveEmbargoesByNode($nid, $ip, $user) {
    $current_user_id = $user->id();
    $active_embargoes = [];
    $embargoes = \Drupal::service('embargoes.embargoes')->getCurrentEmbargoesByNode($nid);
    foreach ($embargoes as $embargo_id) {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);

      $exempt_users = $embargo->getExemptUsers();
      if (!is_null($exempt_users)) {
        $exempt_users_flattened = [];
        foreach ($exempt_users as $exempt_user) {
          $exempt_users_flattened[] = $exempt_user['target_id'];
        }
        if (in_array($current_user_id, $exempt_users_flattened)) {
          $user_is_exempt = TRUE;
        }
        else {
          $user_is_exempt = FALSE;
        }
      }


      $ip_is_exempt = FALSE;


      if ($user_is_exempt == FALSE && $ip_is_exempt == FALSE) {
        $active_embargoes[$embargo_id] = $embargo_id;
      }

    }
    return $active_embargoes;
  }
}
