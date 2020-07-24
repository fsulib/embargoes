<?php

namespace Drupal\embargoes;

/**
 * Interface EmbargoesIpRangesServiceInterface.
 */
interface EmbargoesIpRangesServiceInterface {

  /**
   * Gets the list of IP range entities.
   *
   * @return string[]
   *   An associative array of IP ranges, pairing IDs with labels.
   */
  public function getIpRanges();

  /**
   * Gets the list of IP range entities as form options.
   *
   * @return string[]
   *   An associative array of IP ranges, pairing IDs with labels, including a
   *   NULL option associated with the string 'None'.
   */
  public function getIpRangesAsSelectOptions();

  /**
   * Helper to detect errors in IP range strings.
   *
   * @param string[] $ranges
   *   A list of IP range strings to check.
   *
   * @return string[]
   *   A list of errors detected. If the return array is empty, no errors were
   *   detected.
   */
  public function detectIpRangeStringErrors(array $ranges);

  /**
   * Helper to detect if an IP address is in range of a particular IP range.
   *
   * @param string $ip
   *   The IP address to check.
   * @param string $range_name
   *   The name of the IP range to check the address against.
   *
   * @return bool
   *   TRUE/FALSE depending on whether or not the given IP address falls in the
   *   given range.
   */
  public function isIpInRange($ip, $range_name);

}
