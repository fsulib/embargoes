<?php

namespace Drupal\embargoes\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;

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
  public function isActivelyEmbargoed(EntityInterface $media) {
    $state = parent::isActivelyEmbargoed($media, $this->currentUser);
    $parent_nodes = $this->embargoes->getMediaParentNids($media->id());
    $embargoes = $this->embargoes->getActiveNodeEmbargoesByNids($parent_nodes, $this->request->getClientIp(), $this->currentUser);
    if (!empty($embargoes) && empty($this->embargoes->getIpAllowedEmbargoes($embargoes))) {
      $state = AccessResult::forbidden();
    }
    return $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpEmbargoedRedirectUrl(EntityInterface $media) {
    $parent_nodes = $this->embargoes->getMediaParentNids($media->id());
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
