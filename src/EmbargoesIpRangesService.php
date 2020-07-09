<?php

namespace Drupal\embargoes;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class EmbargoesIpRangesService.
 */
class EmbargoesIpRangesService implements EmbargoesIpRangesServiceInterface {

  /**
   * IP range entity storage instance.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $rangeStorage;

  /**
   * Constructs a new EmbargoesIpRangesService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   EntityTypeManager interface.
   */
  public function __construct(EntityTypeManagerInterface $manager) {
    $this->rangeStorage = $manager->getStorage('embargoes_ip_range_entity');
  }

  /**
   * {@inheritdoc}
   */
  public function getIpRanges() {
    $ip_range_entities = $this->rangeStorage->loadMultiple();
    $ips = [];
    foreach ($ip_range_entities as $ip) {
      $ips[$ip->id()] = $ip->label();
    }
    return $ips;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpRangesAsSelectOptions() {
    $ip_range_entities = $this->rangeStorage->loadMultiple();
    $ips = [];
    $ips[NULL] = 'None';
    foreach ($ip_range_entities as $ip) {
      $ips[$ip->id()] = $ip->label();
    }
    return $ips;
  }

  /**
   * {@inheritdoc}
   */
  public function detectIpRangeStringErrors(array $ranges) {
    $errors = [];
    foreach ($ranges as $range) {
      $range_array = explode("/", trim($range));
      if (count($range_array) != 2) {
        $errors[] = "Invalid range: {$range}";
      }
      else {
        $nets = explode('.', $range_array[0]);
        foreach ($nets as $net) {
          if ((intval($net) == 0 && $net != '0') || intval($net) > 255) {
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

  /**
   * {@inheritdoc}
   */
  public function isIpInRange($ip, $range_name) {
    if ($range_name != 'none') {
      $ranges = $this->rangeStorage->load($range_name)->getRanges();
      $response = FALSE;
      if (!$this->detectIpRangeStringErrors($ranges)) {
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
