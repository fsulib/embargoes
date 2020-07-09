<?php

namespace Drupal\embargoes\Controller;

use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
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
        '#type' => 'markup',
        '#markup' => Markup::create('<p>There are no embargoes on this node.</p>'),
      ];
    }
    else {
      $rows = [];
      foreach ($embargo_ids as $embargo_id) {
        $embargo = $this->entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);

        if ($embargo->getExpirationType() == 0) {
          $expiry = 'Indefinite';
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
            $user_name = $user_entity ? $user_entity->getUserName() : 'Missing User';
            $formatted_users[] = "<a href='/user/{$uid}'>{$user_name}</a>";
          }
        }
        $formatted_exempt_users_row = Markup::create(implode("<br>", $formatted_users));

        if (!is_null($embargo->getExemptIps())) {
          $ip_range = $this->entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps());
          $ip_range_label = $ip_range->label();
          $ip_range_formatted = Markup::create("<a href='/admin/config/content/embargoes/settings/ips/{$embargo->getExemptIps()}/edit'>{$ip_range_label}</a>");
        }
        else {
          $ip_range_formatted = "None";
        }

        $formatted_emails = Markup::create(implode('<br>', $embargo->getAdditionalEmails()));

        $row = [
          'type' => ($embargo->getEmbargoType() == 1 ? 'Node' : 'Files'),
          'expiry' => $expiry,
          'exempt_ips' => $ip_range_formatted,
          'exempt_users' => $formatted_exempt_users_row,
          'additional_emails' => $formatted_emails,
          'edit' => Markup::create("<a href='/node/{$node->id()}/embargoes/{$embargo_id}'>Edit</a><br><a href='/admin/config/content/embargoes/settings/embargoes/{$embargo_id}/delete'>Delete</a>"),
        ];
        array_push($rows, $row);
      }

      $markup['embargoes'] = [
        '#type' => 'table',
        '#header' => [
          'Type',
          'Expiration Date',
          'Exempt IP Range',
          'Exempt Users',
          'Additional Emails',
          'Edit',
        ],
        '#rows' => $rows,
      ];
    }

    $markup['add'] = [
      '#type' => 'markup',
      '#markup' => Markup::create("<p><a href='/node/{$node->id()}/embargoes/add'>Add Embargo</a></p>"),
    ];

    return [
      '#type' => 'markup',
      '#markup' => render($markup),
    ];
  }

}
