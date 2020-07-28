<?php

namespace Drupal\embargoes\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

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
  public function isActivelyEmbargoed(EntityInterface $node, AccountInterface $user) {
    $state = parent::isActivelyEmbargoed($node, $user);
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids([$node->id()], $this->request->getClientIp(), $user);
    if (!empty($embargoes) && empty($this->embargoes->getIpAllowedEmbargoes($embargoes))) {
      $state = AccessResult::forbidden();
    }
    return $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpEmbargoedRedirectUrl(EntityInterface $node, AccountInterface $user) {
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids([$node->id()], $this->request->getClientIp(), $user);
    $ip_allowed_embargoes = $this->embargoes->getIpAllowedEmbargoes($embargoes);
    if (!empty($embargoes) && !empty($ip_allowed_embargoes)) {
      return $this->urlGenerator->generateFromRoute('embargoes.ip_access_denied', [
        'label' => $node->label(),
        'ranges' => $ip_allowed_embargoes,
      ]);
    }
    return NULL;
  }

}
