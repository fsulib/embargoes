<?php

namespace Drupal\embargoes_log_views\Plugin\views\filter;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\views\Plugin\views\filter\InOperator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Exposes embargoes statuses as an 'in' operator filter.
 *
 * @ViewsFilter("embargoes_log_status")
 */
class EmbargoesLogStatus extends InOperator {

  /**
   * An embargoes service object.
   *
   * @var \Drupal\embargoes\EmbargoesEmbargoesServiceInterface
   */
  protected $embargoes;

  /**
   * Embargoes storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $embargoesStorage;

  /**
   * An embargoes storage interface.

  /**
   * Creates a new log statuses filter.
   *
   * @param array $configuration
   *   The filter plugin configuration.
   * @param string $plugin_id
   *   The plugin identifier.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\embargoes\EmbargoesEmbargoesServiceInterface $embargoes
   *   An embargoes service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $embargoes_storage
   *   Embargo entity storage interface.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EmbargoesEmbargoesServiceInterface $embargoes, EntityStorageInterface $embargoes_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->embargoes = $embargoes;
    $this->embargoesStorage = $embargoes_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('embargoes.embargoes'),
      $container->get('entity_type.manager')->getStorage('embargoes_embargo_entity'));
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      // XXX: Create a dummy entity; this will allow us to get the appropriate
      // list of options for the current embargoes_embargo_entity class without
      // having to access an existing embargo. The object will not be saved.
      $dummy = $this->embargoesStorage->create();
      $this->valueOptions = $this->embargoes->getNotificationStatusesAsFormOptions($dummy);
    }
    return $this->valueOptions;
  }
}
