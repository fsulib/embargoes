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

  public function getEmbargoesByNode($nid) {
    $query = \Drupal::entityQuery('embargoes_embargo_entity')
      ->condition('embargoed_node', $nid);
    $embargo_ids = $query->execute();
    return $embargo_ids;
  }

}
