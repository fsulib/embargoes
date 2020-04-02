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
      $ip_is_exempt = \Drupal::service('embargoes.embargoes')->isIpInExemptRange($ip, $embargo_id);
      $user_is_exempt = \Drupal::service('embargoes.embargoes')->isUserInExemptUsers($user, $embargo_id);
      $role_is_exempt = $user->hasPermission('bypass embargoes restrictions');
      if (!$ip_is_exempt && !$user_is_exempt && !$role_is_exempt) {
        $active_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $active_embargoes;
  }

  public function getActiveNodeEmbargoesByNode($nid, $ip, $user) {
    $active_node_embargoes = [];
    $embargoes = \Drupal::service('embargoes.embargoes')->getActiveEmbargoesByNode($nid, $ip, $user);
    foreach ($embargoes as $embargo_id) {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
      if ($embargo->getEmbargoTypeAsInt() == 1) {
        $active_node_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $active_node_embargoes;
  }

  public function isUserInExemptUsers($user, $embargo_id) {
    $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
    $exempt_users = $embargo->getExemptUsers();
    if (is_null($exempt_users)) {
      $user_is_exempt = FALSE;
    }
    else {
      $exempt_users_flattened = [];
      foreach ($exempt_users as $exempt_user) {
        $exempt_users_flattened[] = $exempt_user['target_id'];
      }
      if (in_array($user->id(), $exempt_users_flattened)) {
        $user_is_exempt = TRUE;
      }
      else {
        $user_is_exempt = FALSE;
      }
    }
    return $user_is_exempt;
  }

  public function isIpInExemptRange($ip, $embargo_id) {
    $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
    $range_id = $embargo->getExemptIps();
    if ($range_id == 'none') {
      $ip_is_exempt = FALSE;
    }
    else {
      $ip_is_exempt = \Drupal::service('embargoes.ips')->isIpInRange($ip, $embargo->getExemptIps());
    }
    return $ip_is_exempt;
  }


}
