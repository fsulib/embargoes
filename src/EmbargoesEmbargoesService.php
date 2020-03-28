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

  public function getActiveEmbargoesByNode($nid) {
    $embargoes = \Drupal::service('embargoes.embargoes')->getCurrentEmbargoesByNode($nid);
    return $embargoes;
  }
}
