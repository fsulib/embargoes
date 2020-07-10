<?php

namespace Drupal\embargoes\Controller;

use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmbargoesLogController.
 */
class EmbargoesNodeEmbargoesController extends ControllerBase {

  /**
   * Embargoes service.
   *
   * @var \Drupal\embargoes\EmbargoesEmbargoesServiceInterface
   */
  protected $embargoes;

  /**
   * Constructs an embargoes node controller.
   *
   * @param \Drupal\embargoes\EmbargoesEmbargoesServiceInterface $embargoes
   *   Embargoes service.
   */
  public function __construct(EmbargoesEmbargoesServiceInterface $embargoes) {
    $this->embargoes = $embargoes;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('embargoes.embargoes'));
  }

  /**
   * Gets markup for displaying embargoes on a node.
   *
   * @return array
   *   Renderable array to show the embargoes on a node.
   */
  public function showEmbargoes(NodeInterface $node = NULL) {

    $embargo_ids = $this->embargoes->getAllEmbargoesByNids([$node->id()]);
    if (empty($embargo_ids)) {
      $markup['embargoes'] = [
        '#markup' => $this->t('There are no embargoes on this node.'),
      ];
    }
    else {
      $rows = [];
      foreach ($embargo_ids as $embargo_id) {
        $embargo = $this->entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);

        if ($embargo->getExpirationType() == 0) {
          $expiry = $this->t('Indefinite');
        }
        else {
          $expiry = $embargo->getExpirationDate();
        }

        $formatted_users = [];
        $exempt_users = $embargo->getExemptUsers();
        if (empty($exempt_users)) {
          $formatted_users[] = "None";
        }
        else {
          foreach ($embargo->getExemptUsers() as $user) {
            $uid = $user['target_id'];
            $user_entity = $this->entityTypeManager()->getStorage('user')->load($uid);
            $user_name = $user_entity ? $user_entity->getUserName() : $this->t('Missing User');
            $formatted_users[] = [
              '#type' => 'link',
              '#title' => $user_name,
              '#url' => Url::fromRoute('entity.user.canonical', [
                'user' => $uid,
              ]),
            ];
          }
        }
        $formatted_exempt_users_row = ['data' => $formatted_users];

        if (!is_null($embargo->getExemptIps())) {
          $ip_range = $this->entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps());
          if (!is_null($ip_range)) {
            $ip_range_formatted = [
              '#type' => 'link',
              '#title' => $ip_range->label(),
              '#url' => Url::fromRoute('entity.embargoes_ip_range.edit_form', [
                'id' => $embargo->getExemptIps(),
              ]),
            ];
          }
          else {
            $ip_range_formatted = $this->t('None');
          }
        }
        else {
          $ip_range_formatted = $this->t('None');
        }

        $formatted_emails = [
          '#markup' => implode('<br>', $embargo->getAdditionalEmails()),
        ];

        $row = [
          'type' => ($embargo->getEmbargoType() == 1 ? $this->t('Node') : $this->t('Files')),
          'expiry' => $expiry,
          'exempt_ips' => $ip_range_formatted,
          'exempt_users' => $formatted_exempt_users_row,
          'additional_emails' => $formatted_emails,
          'edit' => [
            '#type' => 'link',
            '#title' => $this->t('Edit'),
            '#url' => Url::fromRoute('entity.embargoes_embargo_entity.edit_form', [
              'id' => $embargo_id,
            ]),
          ],
          'delete' => [
            '#type' => 'link',
            '#title' => $this->t('Delete'),
            '#url' => Url::fromRoute('entity.embargoes_embargo_entity.delete_form', [
              'id' => $embargo_id,
            ]),
          ],
        ];
        array_push($rows, $row);
      }

      $markup['embargoes'] = [
        '#type' => 'table',
        '#header' => [
          $this->t('Type'),
          $this->t('Expiration Date'),
          $this->t('Exempt IP Range'),
          $this->t('Exempt Users'),
          $this->t('Additional Emails'),
          $this->t('Edit'),
          $this->t('Delete'),
        ],
        '#rows' => $rows,
      ];
    }

    $markup['add_embargo'] = [
      '#type' => 'link',
      '#title' => $this->t('Add Embargo'),
      '#link' => Url::fromRoute('entity.embargoes_embargo_entity.add_form'),
    ];

    ksm($markup);

    return $markup;
  }

}
