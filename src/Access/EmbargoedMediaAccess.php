<?php

namespace Drupal\embargoes\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control for embargoed media.
 */
class EmbargoedMediaAccess extends EmbargoedAccessResult {

  /**
   * {@inheritdoc}
   */
  public static function entityType() {
    return 'media';
  }

  /**
   * {@inheritdoc}
   */
  public function isActivelyEmbargoed(EntityInterface $media, AccountInterface $user) {
    $state = parent::isActivelyEmbargoed($media, $user);
    $parent_nodes = $this->embargoes->getMediaParentNids($media->id());
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids($parent_nodes, $this->request->getClientIp(), $user);
    if (!empty($embargoes) && empty($this->embargoes->getIpAllowedEmbargoes($embargoes))) {
      $state = AccessResult::forbidden();
      $state->addCacheableDependency($media);
      $state->addCacheableDependency($user);
    }
    return $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpEmbargoedRedirectUrl(EntityInterface $media, AccountInterface $user) {
    $parent_nodes = $this->embargoes->getMediaParentNids($media->id());
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids($parent_nodes, $this->request->getClientIp(), $user);
    $ip_allowed_embargoes = $this->embargoes->getIpAllowedEmbargoes($embargoes);
    if (!empty($embargoes) && !empty($ip_allowed_embargoes)) {
      return $this->urlGenerator->generateFromRoute('embargoes.ip_access_denied', [
        'label' => $media->label(),
        'ranges' => $ip_allowed_embargoes,
      ]);
    }
    return NULL;
  }

}
