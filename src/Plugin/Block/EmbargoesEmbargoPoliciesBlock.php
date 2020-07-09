<?php

namespace Drupal\embargoes\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "Embargo Policies" block.
 *
 * @Block(
 *   id="embargoes_embargo_policies_block",
 *   admin_label = @Translation("Embargo Policies"),
 *   category = @Translation("Embargoes")
 * )
 */
class EmbargoesEmbargoPoliciesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * A route matching interface.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * An embargoes service.
   *
   * @var \Drupal\embargoes\EmbargoesEmbargoesServiceInterface
   */
  protected $embargoes;

  /**
   * An entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('embargoes.embargoes'),
      $container->get('entity_type.manager'));
  }

  /**
   * Constructs an embargoes policies block.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   A route matching interface.
   * @param \Drupal\embargoes\EmbargoesEmbargoesServiceInterface $embargoes
   *   An embargoes management service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   An entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ResettableStackedRouteMatchInterface $route_match, EmbargoesEmbargoesServiceInterface $embargoes, EntityTypeManagerInterface $entity_manager) {
    parent::construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->embargoes = $embargoes;
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      $embargoes = $this->embargoes->getCurrentEmbargoesByNids([$node->id()]);
      $embargo_count = count($embargoes);
      if (count($embargoes) > 0) {
        $embargo_plurality = ($embargo_count == 1 ? "embargo" : "embargoes");
        $body = "<span id='embargoes_embargo_policy_block_preamble' class='embargoes_embargo_policy_block'>This resource has {$embargo_count} {$embargo_plurality}:</span>";
        foreach ($embargoes as $embargo_id) {
          $body .= "<hr id='embargoes_embargo_policy_block_separator' class='embargoes_embargo_policy_block'><ul class='embargoes_embargo_policy_block embargoes_embargo_policy_block_list'>";
          $embargo = $this->entityManager->getStorage('embargoes_embargo_entity')->load($embargo_id);

          if ($embargo->getExpirationType() == 0) {
            $embargo_expiry = 'Indefinite';
          }
          else {
            $embargo_expiry = "Until {$embargo->getExpirationDate()}";
          }
          $embargo_expiry_string = "<li id='embargoes_embargo_policy_block_expiration_item' class='embargoes_embargo_policy_block embargoes_embargo_policy_block_item'><strong id='embargoes_embargo_policy_block_expiration_label' class='embargoes_embargo_policy_block embargoes_embargo_policy_block_item_label'>Duration:</strong> {$embargo_expiry}</li>";
          $body .= $embargo_expiry_string;

          $embargo_type = ($embargo->getEmbargoType() == 1 ? 'Resource' : 'Resource Files');
          $embargo_type_string = "<li id='embargoes_embargo_policy_block_type_item' class='embargoes_embargo_policy_block embargoes_embargo_policy_block_item'><strong id='embargoes_embargo_policy_block_type_label' class='embargoes_embargo_policy_block embargoes_embargo_policy_block_item_label'>Disallow Access To:</strong> {$embargo_type}</li>";
          $body .= $embargo_type_string;

          if (is_null($embargo->getExemptIps())) {
            $embargo_ips_string = "";
          }
          else {
            $embargo_ips = $this->entityManager->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps())->label();
            $embargo_ips_string = "<li id='embargoes_embargo_policy_block_network_item' class='embargoes_embargo_policy_block embargoes_embargo_policy_block_item'><strong id='embargoes_embargo_policy_block_network_label' class='embargoes_embargo_policy_block embargoes_embargo_policy_block_item_label'>Allowed Networks:</strong> {$embargo_ips}</li>";
          }
          $body .= $embargo_ips_string;

          $body .= "</ul>";
        }
      }
    }

    return [
      '#markup' => Markup::create($body),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return 0;
  }

}
