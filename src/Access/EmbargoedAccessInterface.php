<?php

namespace Drupal\embargoes\Access;

use Drupal\Core\Entity\EntityInterface;

/**
 * Determine whether an item is embargoed and should be accessible.
 */
interface EmbargoedAccessInterface {

  /**
   * Asserts the asset in question is actively embargoed against the user.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to determine embargo status for.
   *
   * @return bool
   *   TRUE or FALSE depending on if the given entity is actively embargoed
   *   against the current user.
   */
  public function isActivelyEmbargoed(EntityInterface $entity);

  /**
   * Sets the message associated with embargoes for this entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to set the embargo message for.
   */
  public function setEmbargoMessage(EntityInterface $entity);

  /**
   * Returns an appropriate response URL for an IP embargoed entity.
   *
   * Returning a response URL here qualifies as an assertion that a redirect
   * response should be made for this resource to the given URL. Return NULL to
   * assert that no redirect should be made.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to get a redirect response for.
   *
   * @return GeneratedUrl|null
   *   An appropriate redirect URL for this object, or NULL if no redirect
   *   should be made.
   */
  public function getIpEmbargoRedirectUrl(EntityInterface $entity);

}
