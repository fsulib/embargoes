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

  public function detectIpRangeStringErrors($string) {
    $errors = [];
    $ranges = explode('|', trim($string));
    foreach ($ranges as $range) {
      $range_array = explode("/", trim($range));
      if (count($range_array) != 2) {
        $errors[] = "Invalid range: {$range}";
      }
      else {
        $nets = explode('.', $range_array[0]);
        foreach ($nets as $net) {
          if ((intval($net) == 0 && $net != '0'  ) || intval($net) > 255) {
            $errors[] = "Invalid net '{$net}' in {$range}";
          }
        }

        if ((intval($range_array[1]) == 0 && $range_array[1] != '0') || intval($range_array[1]) > 32) {
          $errors[] = "Invalid mask '{$range_array[1]}' in {$range}";
        }
      }
    }
    return $errors;
  }

  public function isIpInRange($ip, $range_name) {
    $range_string = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($range_name)->getRange();
    if (\Drupal::service('embargoes.ips')->detectIpRangeStringErrors($range_string)) {
      // This is dumb, but better than revealing an embargoed node because of malformed range settings.
      $response = FALSE;
    }
    else {
      // Check to see if IP is in any of the provided ranges
      $response = TRUE; //Placeholder 
    }
    return $response;
  }
}
