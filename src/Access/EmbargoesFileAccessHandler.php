<?php

namespace Drupal\embargoes\Access;

use Drupal\file\FileAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Extends checkAccess to check embargoed access.
 */
class EmbargoesFileAccessHandler extends FileAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $embargoed = \Drupal::service('embargoes.file_access')->isActivelyEmbargoed($entity, $account);
    if ($embargoed->isForbidden()) {
      return $embargoed;
    }
    return parent::checkAccess($entity, $operation, $account);
  }

}
