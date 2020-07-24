<?php

namespace Drupal\embargoes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Embargo entities.
 */
interface EmbargoesEmbargoEntityInterface extends ConfigEntityInterface {

  /**
   * Returns the type of embargo.
   *
   * @return bool
   *   Either FALSE for a file-level embargo or TRUE for a node-level embargo.
   */
  public function getEmbargoType();

  /**
   * Casts the type of embargo to an integer.
   *
   * @return int
   *   Either 0 for a file-level embargo or 1 for a node-level embargo.
   */
  public function getEmbargoTypeAsInt();

  /**
   * Sets the embargo type.
   *
   * @param bool $type
   *   Either FALSE for an indefinite embargo or TRUE for a node-level embargo.
   *
   * @return bool
   *   The newly-set embargo type.
   */
  public function setEmbargoType($type);

  /**
   * Gets the type of expiration.
   *
   * @return bool
   *   Either FALSE for an indefinite embargo or TRUE for a scheduled embargo.
   */
  public function getExpirationType();

  /**
   * Gets the type of expiration as an integer.
   *
   * @return int
   *   Either 0 for an indefinite embargo or 1 for a scheduled embargo.
   */
  public function getExpirationTypeAsInt();

  /**
   * Sets the expiration type.
   *
   * @param bool $type
   *   Either FALSE for an indefinite embargo or TRUE for a scheduled embargo.
   *
   * @return bool
   *   The newly-set expiration type.
   */
  public function setExpirationType($type);

  /**
   * Gets the date of expiration for a scheduled embargo.
   *
   * @return string
   *   An ISO-8601 timestamp.
   */
  public function getExpirationDate();

  /**
   * Sets the date of expiration for a scheduled embargo.
   *
   * @param string $date
   *   An ISO-8601 timestamp.
   *
   * @return string
   *   The newly-set timestamp.
   */
  public function setExpirationDate($date);

  /**
   * Gets the machine name for the exempt IP range.
   *
   * @return string|null
   *   The ID for a configured exempt IP range, or NULL.
   */
  public function getExemptIps();

  /**
   * Sets the exempt IP range machine name.
   *
   * @param string|null $range
   *   The machine name for a configured exempt IP range, or NULL.
   */
  public function setExemptIps($range);

  /**
   * Gets the list of exempt users.
   *
   * @return int[]
   *   A list of user IDs exempt from the embargo.
   */
  public function getExemptUsers();

  /**
   * Gets the list of exempt users as user entities.
   *
   * @return \Drupal\user\Entity\User[]
   *   A list of user entities exempt from the embargo.
   */
  public function getExemptUsersEntities();

  /**
   * Sets the list of users exempt from this embargo.
   *
   * @param array|bool $users
   *   A list of user IDs exempt from this embargo, or FALSE to set an empty
   *   list.
   *
   * @return array
   *   The newly-set users array.
   */
  public function setExemptUsers($users);

  /**
   * Get the ID of the embargoed node this embargo applies to.
   *
   * @return int
   *   The ID of the embargoed node this embargo applies to.
   */
  public function getEmbargoedNode();

  /**
   * Sets the ID of the node this embargo applies to.
   *
   * @param int $node
   *   The ID of the node this embargo applies to.
   *
   * @return int
   *   The ID of the node this embargo was set to apply to.
   */
  public function setEmbargoedNode($node);

  /**
   * An array of email addresses to be notified in regards to the embargo.
   *
   * @return string[]
   *   An array of email addresses.
   */
  public function getAdditionalEmails();

  /**
   * Sets the list of email addresses to be notified in regards to the embargo.
   *
   * @param string $emails
   *   A list of email addresses to be notified in regards to the embargo.
   *
   * @return string[]
   *   The list of email addresses, parsed into an array.
   */
  public function setAdditionalEmails($emails);

  /**
   * Gets the current notification status of the embargo.
   *
   * @return string
   *   The current notification status of the embargo - one of either 'created',
   *   'updated', 'warned', or 'expired'.
   */
  public function getNotificationStatus();

  /**
   * Sets the current notification status of the embargo.
   *
   * @param string $status
   *   The current notification status of the embargo - one of either 'created',
   *   'updated', 'warned', or 'expired'.
   *
   * @return string
   *   The new notification status.
   */
  public function setNotificationStatus($status);

}
