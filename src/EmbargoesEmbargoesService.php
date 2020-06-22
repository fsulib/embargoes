<?php

namespace Drupal\embargoes;

/**
 * Class EmbargoesEmbargoesService.
 */ class EmbargoesEmbargoesService implements EmbargoesEmbargoesServiceInterface {

  /**
   * Constructs a new EmbargoesEmbargoesService object.
   */
  public function __construct() {
  }

  public function getAllEmbargoesByNids($nids) {
    $all_embargoes = [];
    foreach ($nids as $nid) {
      $query = \Drupal::entityQuery('embargoes_embargo_entity')
        ->condition('embargoed_node', $nid);
      $node_embargoes = $query->execute();
      $all_embargoes = array_merge($all_embargoes, $node_embargoes);
    }
    return $all_embargoes;
  }

  public function getCurrentEmbargoesByNids($nids) {
    $current_embargoes = [];
    $embargoes = \Drupal::service('embargoes.embargoes')->getAllEmbargoesByNids($nids);
    foreach ($embargoes as $embargo_id) {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
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

  public function getIpAllowedCurrentEmbargoesByNids($nids) {
    $ip_allowed_current_embargoes = [];
    $embargoes = \Drupal::service('embargoes.embargoes')->getCurrentEmbargoesByNids($nids);
    foreach ($embargoes as $embargo_id) {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
      if ($embargo->getExemptIps() != 'none') {
        $ip_allowed_current_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $ip_allowed_current_embargoes;
  }




  public function getActiveEmbargoesByNids($nids, $ip, $user) {
    $current_user_id = $user->id();
    $active_embargoes = [];
    $embargoes = \Drupal::service('embargoes.embargoes')->getCurrentEmbargoesByNids($nids);
    foreach ($embargoes as $embargo_id) {
      $ip_is_exempt = \Drupal::service('embargoes.embargoes')->isIpInExemptRange($ip, $embargo_id);
      $user_is_exempt = \Drupal::service('embargoes.embargoes')->isUserInExemptUsers($user, $embargo_id);
      $role_is_exempt = $user->hasPermission('bypass embargoes restrictions');
      if (!$ip_is_exempt && !$user_is_exempt && !$role_is_exempt) {
        $active_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $active_embargoes;
  }

  public function getActiveNodeEmbargoesByNids($nids, $ip, $user) {
    $active_node_embargoes = [];
    $embargoes = \Drupal::service('embargoes.embargoes')->getActiveEmbargoesByNids($nids, $ip, $user);
    foreach ($embargoes as $embargo_id) {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
      if ($embargo->getEmbargoTypeAsInt() == 1) {
        $active_node_embargoes[$embargo_id] = $embargo_id;
      }
    }
    return $active_node_embargoes;
  }

  public function getIpAllowedEmbargoes($embargoes) {
    $ip_allowed_embargoes = [];
    foreach ($embargoes as $embargo_id) {
      $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
      if ($embargo->getExemptIps() != 'none') {
        $ip_allowed_embargoes[$embargo_id] = $embargo->getExemptIps();
      }
    }
    return $ip_allowed_embargoes;
  }



  public function isUserInExemptUsers($user, $embargo_id) {
    $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
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

  public function isIpInExemptRange($ip, $embargo_id) {
    $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);
    $range_id = $embargo->getExemptIps();
    if ($range_id == 'none') {
      $ip_is_exempt = FALSE;
    }
    else {
      $ip_is_exempt = \Drupal::service('embargoes.ips')->isIpInRange($ip, $embargo->getExemptIps());
    }
    return $ip_is_exempt;
  }

  public function getNodeMediaReferenceFields() {
    $efm = \Drupal::service('entity_field.manager');
    $entity_fields = array_keys($efm->getFieldMapByFieldType('entity_reference')['node']);
    $media_fields = [];
    foreach ($entity_fields as $field) {
      if (strpos($field, 'field_') === 0) {
        $field_data = \Drupal\field\Entity\FieldStorageConfig::loadByName('node', $field);
        if ($field_data->getSetting('target_type') == 'media') {
          $media_fields[] = $field;
        }
      }
    }
    return $media_fields;
  }

  public function getMediaParentNids($mid) {
    $media_entity = \Drupal::entityTypeManager()->getStorage('media')->load($mid);
    if ($media_entity->hasField('field_media_of')) {
      $nid = $media_entity->get('field_media_of')->getString();
      $nids = array($nid);
    } 
    else {
      $media_fields = \Drupal::service('embargoes.embargoes')->getNodeMediaReferenceFields();
      $query = \Drupal::entityQuery('node');
      $group = $query->orConditionGroup();
      foreach ($media_fields as $field) {
        $group->condition($field, $mid);
      }
      $result = $query->condition($group)->execute();
      $nids = array_values($result);
    }
    return $nids;
  }

  public function getParentNidsOfFileEntity($file) {
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
                $nids = array(array_keys($value)[0]);
                break;
              case 'media':
                $mid = array_keys($value)[0];
                $nids = \Drupal::service('embargoes.embargoes')->getMediaParentNids($mid);
                break;
            }
          }
        }
      }
    }
    return $nids;
  }

}
