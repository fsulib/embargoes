<?php

namespace Drupal\embargoes;

/**
 * Interface EmbargoesLogServiceInterface.
 */
interface EmbargoesLogServiceInterface {

  /**
   * Logs an embargo event.
   *
   * @param array $values
   *   Associative array of keys and values that should be applied to the log.
   */
  public function logEmbargoEvent(array $values);

}
