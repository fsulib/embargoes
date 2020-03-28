<?php

namespace Drupal\embargoes;

/**
 * Interface EmbargoesEmbargoesServiceInterface.
 */
interface EmbargoesEmbargoesServiceInterface {

  public function getAllEmbargoesByNode($node);
  public function getCurrentEmbargoesByNode($node);
  public function getActiveEmbargoesByNode($node, $ip, $user);

}
