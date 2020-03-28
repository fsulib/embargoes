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
    $embargoes = \Drupal::service('embargoes.embargoes')->getAllEmbargoesByNode($nid);
    return $embargoes;
  }

  public function getActiveEmbargoesByNode($nid) {
    $embargoes = \Drupal::service('embargoes.embargoes')->getCurrentEmbargoesByNode($nid);
    return $embargoes;
  }
}
