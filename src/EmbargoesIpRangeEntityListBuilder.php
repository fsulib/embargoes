<?php

namespace Drupal\embargoes;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a listing of IP Range entities.
 */
class EmbargoesIpRangeEntityListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('IP Range');
    $header['id'] = $this->t('Machine name');
    $header['range'] = $this->t('Range');
    $header['proxy_url'] = $this->t('Proxy URL');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['range'] = Markup::create(str_replace('|', '<br>', $entity->getRange()));
    $row['proxy_url'] = $entity->getProxyUrl();
    return $row + parent::buildRow($entity);
  }

}
