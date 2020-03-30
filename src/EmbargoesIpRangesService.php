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
    if ($range_name != 'none') {
      $range_string = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($range_name)->getRange();
      $response = FALSE;
      if (!\Drupal::service('embargoes.ips')->detectIpRangeStringErrors($range_string)) {
        $ranges = explode('|', trim($range_string));
        foreach ($ranges as $range) {
          list($net, $mask) = explode("/", trim($range));
          $ip_net = ip2long($net);
          $ip_mask = ~((1 << (32 - $mask)) - 1);
          $ip_ip = ip2long($ip);
          $ip_ip_net = $ip_ip & $ip_mask;
          if ($ip_ip_net == $ip_net) {
            $response = TRUE;
          }
        }
      }
    }
    return $response;
  }
}
