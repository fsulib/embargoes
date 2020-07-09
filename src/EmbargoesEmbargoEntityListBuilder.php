<?php

namespace Drupal\embargoes;

use Drupal\user\Entity\User;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a listing of Embargo entities.
 */
class EmbargoesEmbargoEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Embargo ID');
    $header['embargo_type'] = $this->t('Embargo Type');
    $header['expiration_type'] = $this->t('Expiration Type');
    $header['expiration_date'] = $this->t('Expiration Date');
    $header['exempt_ips'] = $this->t('Exempt IP Range');
    $header['exempt_users'] = $this->t('Exempt Users');
    $header['additional_emails'] = $this->t('Additional Emails');
    $header['notification_status'] = $this->t('Notification Status');
    $header['embargoed_node'] = $this->t('Embargoed Node');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {

    $formatted_users = [];
    foreach ($entity->getExemptUsers() as $user) {
      $uid = $user['target_id'];
      $user_entity = User::load($uid);
      $user_name = $user_entity->getUserName();
      $formatted_users[] = "<a href='/user/{$uid}'>{$user_name}</a>";

    }
    $formatted_exempt_users_row = Markup::create(implode("<br>", $formatted_users));

    $nid = $entity->getEmbargoedNode();
    $node = node_load($nid);
    $node_title = $node->title->value;
    $formatted_node_row = Markup::create("<a href='/node/{$nid}'>{$node_title}</a>");

    $ip_range = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($entity->getExemptIps());
    if (!is_null($ip_range)) {
      $ip_range_label = $ip_range->label();
      $ip_range_formatted = Markup::create("<a href='/admin/structure/embargoes_ip_range_entity/{$entity->getExemptIps()}/edit'>{$ip_range_label}</a>");
    }
    else {
      $ip_range_formatted = "None";
    }

    $formatted_emails = Markup::create(implode('<br>', $entity->getAdditionalEmails()));

    $row['id'] = $entity->id();
    $row['embargo_type'] = ($entity->getEmbargoType() == 1 ? 'Node' : 'Files');
    $row['expiration_type'] = ($entity->getExpirationType() == 1 ? 'Scheduled' : 'Indefinite');
    $row['expiration_date'] = (!empty($entity->getExpirationDate()) ? $entity->getExpirationDate() : 'None');
    $row['exempt_ips'] = $ip_range_formatted;
    $row['exempt_users'] = $formatted_exempt_users_row;
    $row['additional_emails'] = $formatted_emails;
    $row['notification_status'] = ucfirst($entity->getNotificationStatus());
    $row['embargoed_node'] = $formatted_node_row;
    return $row + parent::buildRow($entity);
  }

}
