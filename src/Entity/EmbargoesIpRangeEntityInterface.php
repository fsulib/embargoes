<?php

namespace Drupal\embargoes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining IP Range entities.
 */
interface EmbargoesIpRangeEntityInterface extends ConfigEntityInterface {

  /**
   * Gets the ID of this IP range.
   *
   * @return string
   *   The unique identifier of this IP range.
   */
  public function id();

  /**
   * Gets the label used for this IP range.
   *
   * @return string
   *   The label to be used for this IP range.
   */
  public function label();

  /**
   * Gets the list of IP ranges inclusive in this range.
   *
   * @return string[]
   *   An array of CIDR IP ranges.
   */
  public function getRanges();

  /**
   * Sets the list of IP ranges inclusive in this range.
   *
   * @param string $ranges
   *   String representing the list of ranges to apply.
   *
   * @return array
   *   The list of ranges, parsed into an array.
   */
  public function setRanges($ranges);

  /**
   * Gets the proxy URL for this IP range.
   *
   * @return string
   *   A URL to use as a proxy for this range, to refer users to a location
   *   representative of the network blocked by this range.
   */
  public function getProxyUrl();

  /**
   * Sets the proxy URL for this IP range.
   *
   * @param string $proxy_url
   *   A URL to use as a proxy for this range, to refer users to a location
   *   representative of the network blocked by this range.
   *
   * @return string
   *   The newly-set proxy URL.
   */
  public function setProxyUrl($proxy_url);

}
