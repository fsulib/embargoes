<?php

namespace Drupal\embargoes\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Render\Markup;

/**
 * Provides a "Embargo Policies" block.
 *
 * @Block(
 *   id="embargoes_embargo_policies_block",
 *   admin_label = @Translation("Embargo Policies"),
 *   category = @Translation("Embargoes")
 * )
 */
class EmbargoesEmbargoPoliciesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $embargoes = \Drupal::service('embargoes.embargoes')->getCurrentEmbargoesByNids(array($node->id()));
      $embargo_count = count($embargoes);
      if (count($embargoes) > 0) {
        $embargo_plurality = ($embargo_count == 1 ? "embargo" : "embargoes");
        $body = "<span id='embargoes_embargo_policy_block_preamble' class='embargoes_embargo_policy_block'>This resource has {$embargo_count} {$embargo_plurality}:</span>";
        foreach ($embargoes as $embargo_id) {
          $body .= "<hr id='embargoes_embargo_policy_block_separator' class='embargoes_embargo_policy_block'><ul class='embargoes_embargo_policy_block embargoes_embargo_policy_block_list'>";
          $embargo = \Drupal::entityTypeManager()->getStorage('embargoes_embargo_entity')->load($embargo_id);

          if ($embargo->getExpirationType() == 0 ) {
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

          if ($embargo->getExemptIps() == 'none') {
            $embargo_ips_string = "";
          }
          else {
            $embargo_ips =\Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps())->label(); 
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

  public function getCacheMaxAge() {
    return 0;
  }

}
