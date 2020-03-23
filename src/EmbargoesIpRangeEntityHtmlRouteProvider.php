<?php

namespace Drupal\embargoes;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides routes for IP Range entities.
 *
 * @see Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class EmbargoesIpRangeEntityHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);

    // Provide your custom entity routes here.
    return $collection;
  }

}
