<?php

namespace Drupal\embargoes\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;

/**
 * Access control for embargoed nodes.
 */
class EmbargoedNodeAccess extends EmbargoedAccessResult {

  /**
   * {@inheritdoc}
   */
  public static function entityType() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public function isActivelyEmbargoed(EntityInterface $node) {
    $state = parent::isActivelyEmbargoed($node, $this->currentUser);
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids([$node->id()], $this->request->getClientIp(), $this->currentUser);
    if (!empty($embargoes) && empty($this->embargoes->getIpAllowedEmbargoes($embargoes))) {
      $state = AccessResult::forbidden();
    }
    return $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpEmbargoedRedirectUrl(EntityInterface $node) {
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids([$node->id()], $this->request->getClientIp(), $this->currentUser);
    $ip_allowed_embargoes = $this->embargoes->getIpAllowedEmbargoes($embargoes);
    if (!empty($embargoes) && !empty($ip_allowed_embargoes)) {
      return $this->urlGenerator->generateFromRoute('embargoes.ip_access_denied', [
        'query' => [
          'path' => $this->request->getRequestUri(),
          'ranges' => $ip_allowed_embargoes,
        ],
      ]);
    }
    return NULL;
  }

}
