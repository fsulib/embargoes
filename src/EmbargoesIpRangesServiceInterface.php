<?php

namespace Drupal\embargoes;

/**
 * Interface EmbargoesIpRangesServiceInterface.
 */
interface EmbargoesIpRangesServiceInterface {

  public function getIpRanges();
  public function getIpRangesAsSelectOptions();
  public function detectIpRangeStringErrors($string);
  public function isIpInRange($ip, $range_name);

}
