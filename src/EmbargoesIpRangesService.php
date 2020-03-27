<?php

namespace Drupal\embargoes;

/**
 * Class EmbargoesIpRangesService.
 */
class EmbargoesIpRangesService implements EmbargoesIpRangesServiceInterface {

  /**
   * Constructs a new EmbargoesIpRangesService object.
   */
  public function __construct() {

  }

  public function getIpRanges() {
    $ip_range_entities = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->loadMultiple();
    $ips = [];
    foreach ($ip_range_entities as $ip) {
      $ips[$ip->id()] = $ip->label();
    }
    return $ips;
  }

  public function getIpRangesAsSelectOptions() {
    $ip_range_entities = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->loadMultiple();
    $ips = [];
    $ips['none'] = 'None';
    foreach ($ip_range_entities as $ip) {
      $ips[$ip->id()] = $ip->label();
    }
    return $ips;
  }


}
