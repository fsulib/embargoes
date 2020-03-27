<?php

namespace Drupal\embargoes;

/**
 * Interface EmbargoesIpRangesServiceInterface.
 */
interface EmbargoesIpRangesServiceInterface {

  public function getIpRanges();
  public function getIpRangesAsSelectOptions();

}
