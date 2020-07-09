<?php

namespace Drupal\embargoes\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;

/**
 * Access control for files attached to embargoed nodes.
 */
class EmbargoedFileAccess extends EmbargoedAccessResult {

  /**
   * {@inheritdoc}
   */
  public static function entityType() {
    return 'file';
  }

  /**
   * {@inheritdoc}
   */
  public function isActivelyEmbargoed(EntityInterface $file) {
    $state = parent::isActivelyEmbargoed($file, $this->currentUser);
    $parent_nodes = $this->embargoes->getParentNidsOfFileEntity($file);
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids($parent_nodes, $this->request->getClientIp(), $this->currentUser);
    if (!empty($embargoes) && empty($this->embargoes->getIpAllowedEmbargoes($embargoes))) {
      $state = AccessResult::forbidden();
    }
    return $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpEmbargoedRedirectUrl(EntityInterface $file) {
    $parent_nodes = $this->embargoes->getParentNidsOfFileEntity($file);
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids($parent_nodes, $this->request->getClientIp(), $this->currentUser);
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
