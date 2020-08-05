<?php

namespace Drupal\embargoes;

use Drupal\file\FileInterface;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class EmbargoesEmbargoesService.
 */
class EmbargoesEmbargoesService implements EmbargoesEmbargoesServiceInterface {

  /**
   * An entity type manager interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * An entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * An embargo IP ranges service.
   *
   * @var \Drupal\embargoes\EmbargoesIpRangesServiceInterface
   */
  protected $ipRanges;

  /**
   * Constructs a new EmbargoesEmbargoesService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $manager
   *   An entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager
   *   An entity field manager.
   * @param \Drupal\embargoes\EmbargoesIpRangesServiceInterface $ip_ranges
   *   An embargoes IP range service.
   */
  public function __construct(EntityTypeManagerInterface $manager, EntityFieldManagerInterface $field_manager, EmbargoesIpRangesServiceInterface $ip_ranges) {
    $this->entityManager = $manager;
    $this->fieldManager = $field_manager;
    $this->ipRanges = $ip_ranges;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllEmbargoesByNids(array $nids) {
    $all_embargoes = [];
    foreach ($nids as $nid) {
      $node_embargoes = $this->entityManager
        ->getStorage('embargoes_embargo_entity')
        ->getQuery()
        ->condition('embargoed_node', $nid)
        ->execute();
      $all_embargoes = array_merge($all_embargoes, $node_embargoes);
    }
    return $all_embargoes;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentEmbargoesByNids(array $nids) {
    $current_embargoes = [];
    $embargoes = $this->getAllEmbargoesByNids($nids);
    foreach ($embargoes as $embargo_id) {
      $embargo = $this->entityManager
        ->getStorage('embargoes_embargo_entity')
        ->load($embargo_id);
      if ($embargo->getExpirationTypeAsInt() == 0) {
        $current_embargoes[$embargo_id] = $embargo_id;
      }
      else {
        $now = time();
        $expiry = strtotime($embargo->getExpirationDate());
        if ($expiry > $now) {
          $current_embargoes[$embargo_id] = $embargo_id;
        }
      }
    }
    return $current_embargoes;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpAllowedCurrentEmbargoesByNids(array $nids) {
    $ip_allowed_current_embargoes = [];
    $embargoes = $this->getCurrentEmbargoesByNids($nids);
    foreach ($embargoes as $embargo_id) {
      $embargo = $this->entityManager
        ->getStorage('embargoes_embargo_entity')
        ->load($embargo_id);
      if (!empty($embargo->getExemptIps())) {
        $ip_allowed_current_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $ip_allowed_current_embargoes;
  }

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
   *   apply to the user to that same ID. An embargo does not apply to the user
   *   if any of the following conditions are true:
   *   - The embargo is exempted by the given $ip
   *   - The user is in the list of exempt users for the embargo
   *   - The user has the 'bypass embargoes restrictions' permission
   */
  public function getActiveEmbargoesByNids(array $nids, $ip, AccountInterface $user) {
    $active_embargoes = [];
    $embargoes = $this->getCurrentEmbargoesByNids($nids);
    foreach ($embargoes as $embargo_id) {
      $ip_is_exempt = $this->isIpInExemptRange($ip, $embargo_id);
      $user_is_exempt = $this->isUserInExemptUsers($user, $embargo_id);
      $role_is_exempt = $user->hasPermission('bypass embargoes restrictions');
      if (!$ip_is_exempt && !$user_is_exempt && !$role_is_exempt) {
        $active_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $active_embargoes;
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveNodeEmbargoesByNids(array $nids, $ip, AccountInterface $user) {
    $active_node_embargoes = [];
    $embargoes = $this->getActiveEmbargoesByNids($nids, $ip, $user);
    foreach ($embargoes as $embargo_id) {
      $embargo = $this->entityManager
        ->getStorage('embargoes_embargo_entity')
        ->load($embargo_id);
      if ($embargo->getEmbargoTypeAsInt() == 1) {
        $active_node_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $active_node_embargoes;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpAllowedEmbargoes(array $embargoes) {
    $ip_allowed_embargoes = [];
    foreach ($embargoes as $embargo_id) {
      $embargo = $this->entityManager
        ->getStorage('embargoes_embargo_entity')
        ->load($embargo_id);
      if (!empty($embargo->getExemptIps())) {
        $ip_allowed_embargoes[$embargo_id] = $embargo->getExemptIps();
      }
    }
    return $ip_allowed_embargoes;
  }

  /**
   * {@inheritdoc}
   */
  public function isUserInExemptUsers(AccountInterface $user, $embargo_id) {
    $embargo = $this->entityManager
      ->getStorage('embargoes_embargo_entity')
      ->load($embargo_id);
    $exempt_users = $embargo->getExemptUsers();
    if (is_null($exempt_users)) {
      $user_is_exempt = FALSE;
    }
    else {
      $exempt_users_flattened = [];
      foreach ($exempt_users as $exempt_user) {
        $exempt_users_flattened[] = $exempt_user['target_id'];
      }
      if (in_array($user->id(), $exempt_users_flattened)) {
        $user_is_exempt = TRUE;
      }
      else {
        $user_is_exempt = FALSE;
      }
    }
    return $user_is_exempt;
  }

  /**
   * {@inheritdoc}
   */
  public function isIpInExemptRange($ip, $embargo_id) {
    $embargo = $this->entityManager
      ->getStorage('embargoes_embargo_entity')
      ->load($embargo_id);
    $range_id = $embargo->getExemptIps();
    if (is_null($range_id)) {
      $ip_is_exempt = FALSE;
    }
    else {
      $ip_is_exempt = $this->ipRanges->isIpInRange($ip, $range_id);
    }
    return $ip_is_exempt;
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeMediaReferenceFields() {
    $entity_fields = array_keys($this->fieldManager->getFieldMapByFieldType('entity_reference')['node']);
    $media_fields = [];
    foreach ($entity_fields as $field) {
      if (strpos($field, 'field_') === 0) {
        $field_data = FieldStorageConfig::loadByName('node', $field);
        if ($field_data->getSetting('target_type') == 'media') {
          $media_fields[] = $field;
        }
      }
    }
    return $media_fields;
  }

  /**
   * Gets a list of nodes that are the parent of the given media ID.
   *
   * @param int $mid
   *   The ID of the media entity to get parents for.
   *
   * @return int[]
   *   A list of node IDs that are parents of the given media. A node is a
   *   parent of the given media if either:
   *   - The media implements and has one or more valid values for
   *     field_media_of, or
   *   - Any node implements an entity_reference field that targets media, and
   *     contains a value targeting the given $mid
   */
  public function getMediaParentNids($mid) {
    $media_entity = $this->entityManager
      ->getStorage('media')
      ->load($mid);
    if ($media_entity && $media_entity->hasField('field_media_of')) {
      $nid = $media_entity->get('field_media_of')->getString();
      $nids = [$nid];
    }
    else {
      $media_fields = $this->getNodeMediaReferenceFields();
      $query = $this->entityManager
        ->getStorage('node')
        ->getQuery();
      $group = $query->orConditionGroup();
      foreach ($media_fields as $field) {
        $group->condition($field, $mid);
      }
      $result = $query->condition($group)->execute();
      $nids = array_values($result);
    }
    return $nids;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentNidsOfFileEntity(FileInterface $file) {
    $relationships = file_get_file_references($file);
    if (!$relationships) {
      $nids = [];
    }
    else {
      foreach ($relationships as $relationship) {
        if (!$relationship) {
          $nids = [];
        }
        else {
          foreach ($relationship as $key => $value) {
            switch ($key) {
              case 'node':
                $nids = [array_keys($value)[0]];
                break;

              case 'media':
                $mid = array_keys($value)[0];
                $nids = $this->getMediaParentNids($mid);
                break;
            }
          }
        }
      }
    }
    return $nids;
  }

}
