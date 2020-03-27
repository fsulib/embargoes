<?php

namespace Drupal\embargoes;

/**
 * Interface EmbargoesLogServiceInterface.
 */
interface EmbargoesLogServiceInterface {

  public function logEmbargoEvent($values);

}
