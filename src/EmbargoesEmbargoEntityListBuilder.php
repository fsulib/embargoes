<?php

namespace Drupal\embargoes;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a listing of Embargo entities.
 */
class EmbargoesEmbargoEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Embargo');
    $header['id'] = $this->t('Machine name');
    $header['embargo_type'] = $this->t('Embargo Type');
    $header['expiration_type'] = $this->t('Expiration Type');
    $header['expiration_date'] = $this->t('Expiration Date');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['embargo_type'] = ($entity->getEmbargoType() == 1 ? 'Node' : 'Files');
    $row['expiration_type'] = ($entity->getExpirationType() == 1 ? 'Indefinite' : 'Scheduled');
    $row['expiration_date'] = $entity->getExpirationDate();
    //$row['range'] = Markup::create(str_replace('|', '<br>', $entity->getRange()));
    return $row + parent::buildRow($entity);
  }

}
