<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\NodeStorageInterface;
use Drupal\Core\Entity\UserStorageInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of Embargo entities.
 */
class EmbargoesEmbargoEntityListBuilder extends ConfigEntityListBuilder implements EntityHandlerInterface {

  /**
   * User account storage.
   *
   * @var \Drupal\Core\Entity\UserStorageInterface
   */
  protected $user;

  /**
   * Node storage.
   *
   * @var \Drupal\Core\Entity\NodeStorageInterface
   */
  protected $node;

  /**
   * Embargoes IP range storage.
   *
   * @var \Drupal\embargoes\Entity\EmbargoesIpRangeEntityInterface
   */
  protected $ipRanges;

  /**
   * URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Create an embargo entity list builder.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   An entity type interface for embargoes.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   Entity storage for embargoes.
   * @param \Drupal\Core\Entity\UserStorageInterface $user
   *   User storage.
   * @param \Drupal\Core\Entity\NodeStorageInterface $node
   *   Node storage.
   * @param \Drupal\embargoes\EmbargoesIpRangeEntityInterface $ip_ranges
   *   IP range entity interface.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   A URL generator.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, UserStorageInterface $user, NodeStorageInterface $node, EmbargoesIpRangeEntityInterface $ip_ranges, UrlGeneratorInterface $url_generator) {
    parent::__construct($entity_type, $storage);
    $this->user = $user;
    $this->node = $node;
    $this->ipRanges = $ip_ranges;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('entity_type.manager')->getStorage('embargoes_ip_range_entity'),
      $container->get('url_generator'));
  }

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
      $user_entity = $this->user->load($uid);
      $formatted_users[] = [
        '#type' => 'link',
        '#title' => $user_entity->getUserName(),
        '#url' => $this->urlGenerator->generateFromRoute('entity.user.canonical', [
          'user' => $uid,
        ]),
      ];

    }
    $formatted_exempt_users_row = ['data' => $formatted_users];

    $nid = $entity->getEmbargoedNode();
    $node = $this->node->load($nid);
    $formatted_node_row = [
      '#type' => 'link',
      '#title' => $node->title->value,
      '#url' => $this->urlGenerator->generateFromRoute('entity.node.canonical', [
        'node' => $nid,
      ]),
    ];

    $ip_range = $this->ipRanges->load($entity->getExemptIps());
    if (!is_null($ip_range)) {
      $ip_range_formatted = [
        '#type' => 'link',
        '#title' => $ip_range->label(),
        '#url' => $this->urlGenerator->generateFromRoute('entity.embargoes_ip_range_entity.add_form', [
          'id' => $entity->getExemptIps(),
        ]),
      ];
    }
    else {
      $ip_range_formatted = "None";
    }

    $formatted_emails = [
      '#markup' => implode('<br>', $entity->getAdditionalEmails()),
    ];

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
