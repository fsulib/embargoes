<?php

namespace Drupal\embargoes\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Embargo IP Range Entity entity.
 *
 * @ConfigEntityType(
 *   id = "embargo_ip_range_entity",
 *   label = @Translation("Embargo IP Range Entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\embargoes\EmbargoIpRangeEntityListBuilder",
 *     "form" = {
 *       "add" = "Drupal\embargoes\Form\EmbargoIpRangeEntityForm",
 *       "edit" = "Drupal\embargoes\Form\EmbargoIpRangeEntityForm",
 *       "delete" = "Drupal\embargoes\Form\EmbargoIpRangeEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\embargoes\EmbargoIpRangeEntityHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "embargo_ip_range_entity",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/embargo_ip_range_entity/{embargo_ip_range_entity}",
 *     "add-form" = "/admin/structure/embargo_ip_range_entity/add",
 *     "edit-form" = "/admin/structure/embargo_ip_range_entity/{embargo_ip_range_entity}/edit",
 *     "delete-form" = "/admin/structure/embargo_ip_range_entity/{embargo_ip_range_entity}/delete",
 *     "collection" = "/admin/structure/embargo_ip_range_entity"
 *   }
 * )
 */
class EmbargoIpRangeEntity extends ConfigEntityBase implements EmbargoIpRangeEntityInterface {

  /**
   * The Embargo IP Range Entity ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Embargo IP Range Entity label.
   *
   * @var string
   */
  protected $label;

}
