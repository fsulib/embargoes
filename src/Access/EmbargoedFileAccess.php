<?php

namespace Drupal\embargoes\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

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
  public function isActivelyEmbargoed(EntityInterface $file, AccountInterface $user) {
    $state = parent::isActivelyEmbargoed($file, $user);
    $parent_nodes = $this->embargoes->getParentNidsOfFileEntity($file);
    $embargoes = $this->embargoes->getActiveEmbargoesByNids($parent_nodes, $this->request->getClientIp(), $user);
    if (!empty($embargoes)) {
      $state = AccessResult::forbidden();
      $state->addCacheableDependency($file);
      $state->addCacheableDependency($user);
    }
    return $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpEmbargoedRedirectUrl(EntityInterface $file, AccountInterface $user) {
    $parent_nodes = $this->embargoes->getParentNidsOfFileEntity($file);
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids($parent_nodes, $this->request->getClientIp(), $user);
    $ip_allowed_embargoes = $this->embargoes->getIpAllowedEmbargoes($embargoes);
    if (!empty($embargoes) && !empty($ip_allowed_embargoes)) {
      return $this->urlGenerator->generateFromRoute('embargoes.ip_access_denied', [
        'label' => $file->label(),
        'ranges' => $ip_allowed_embargoes,
      ]);
    }
    return NULL;
  }

}
