<?php

namespace Drupal\embargoes;

use Drupal\file\FileInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface EmbargoesEmbargoesServiceInterface.
 */
interface EmbargoesEmbargoesServiceInterface {

  /**
   * Gets a list of all embargoes that apply to the given node IDs.
   *
   * @param int[] $nids
   *   The list of node IDs to get embargoes for.
   *
   * @return int[]
   *   An array of embargo entity IDs for the given nodes.
   */
  public function getAllEmbargoesByNids(array $nids);

  /**
   * Gets a list of unexpired embargoes that apply to the given node IDs.
   *
   * @param int[] $nids
   *   The list of node IDs to get active embargoes for.
   *
   * @return int[]
   *   An array of active embargo entity IDs for the given nodes.
   */
  public function getCurrentEmbargoesByNids(array $nids);

  /**
   * Gets embargoes for the given node IDs that have attached IP ranges.
   *
   * @param int[] $nids
   *   The list of node IDs to get IP-allowed embargoes for.
   *
   * @return int[]
   *   An array of embargoes applied to the given node IDs that have attached
   *   IP ranges.
   */
  public function getIpAllowedCurrentEmbargoesByNids(array $nids);

  /**
   * Gets embargoes for the given node IDs that apply to the given user.
   *
   * @param int[] $nids
   *   The list of node IDs to query against.
   * @param string $ip
   *   An IP address to test against for these embargoes.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user entity to test embargoes against.
   *
   * @return int[]
   *   An associative array mapping any embargoes from the given node IDs that
   *   apply to the user to that same node ID.
   */
  public function getActiveEmbargoesByNids(array $nids, $ip, AccountInterface $user);

  /**
   * Gets node-level embargoes for the given node IDs that apply to the user.
   *
   * @param int[] $nids
   *   The list of node IDs to query against.
   * @param string $ip
   *   An IP address to test against for these embargoes.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user entity to test embargoes against.
   *
   * @return int[]
   *   An associative array mapping any embargoes from the given node IDs that
   *   apply to the user to that same node ID, filtered to only include
   *   embargoes that apply to the node itself.
   */
  public function getActiveNodeEmbargoesByNids(array $nids, $ip, AccountInterface $user);

  /**
   * Filters and returns exempt IP ranges for the given embargo IDs.
   *
   * @param int[] $embargoes
   *   A list of embargo entity IDs to check IP ranges for.
   *
   * @return string[]
   *   An associative array containing any $embargoes that have a configured
   *   exempt IP range, paired with the ID for that exempt IP range.
   */
  public function getIpAllowedEmbargoes(array $embargoes);

  /**
   * Determines whether a given $user is in the exemption list for an embargo.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user entity to test against.
   * @param int $embargo_id
   *   The embargo to check the list of exempt users for.
   *
   * @return bool
   *   TRUE or FALSE, depending on whether or not the given user is in the list
   *   of exempt users for the given embargo.
   */
  public function isUserInExemptUsers(AccountInterface $user, $embargo_id);

  /**
   * Determines whether an IP address is in the exemption range for an embargo.
   *
   * @param string $ip
   *   An IP address to check against.
   * @param int $embargo_id
   *   The embargo to check IP exemptions for.
   *
   * @return bool
   *   TRUE or FALSE, depending on whether or not the given IP address falls
   *   within the exemption range for the given embargo.
   */
  public function isIpInExemptRange($ip, $embargo_id);

  /**
   * Gets a list of entity_reference fields that target media.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   A list of entity_reference fields that target media entities.
   */
  public function getNodeMediaReferenceFields();

  /**
   * Gets a list of nodes that are the parent of the given media ID.
   *
   * @param int $mid
   *   The ID of the media entity to get parents for.
   *
   * @return int[]
   *   A list of node IDs that are parents of the given media.
   */
  public function getMediaParentNids($mid);

  /**
   * Gets a list of parent node IDs for the given file entity.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity to get parent node IDs for.
   *
   * @return int[]
   *   An array of node IDs that are parents of the given file.
   */
  public function getParentNidsOfFileEntity(FileInterface $file);

}
