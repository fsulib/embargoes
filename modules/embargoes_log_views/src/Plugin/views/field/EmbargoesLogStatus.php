<?php

namespace Drupal\embargoes_log_views\Plugin\views\field;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Converts action integers to strings.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("embargoes_log_status")
 */
class EmbargoesLogStatus extends FieldPluginBase {

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
   * Creates a new log statuses field.
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
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['replace_action_integer'] = [
      'default' => TRUE,
    ];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $form['replace_action_integer'] = [
      '#title' => $this->t("Replace action integer"),
      '#description' => $this->t("The action that was taken with an embargo is stored as an integer; replace this with the actual name."),
      '#type' => 'checkbox',
      '#default_value' => $this->options['replace_action_integer'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    if ($this->options['replace_action_integer']) {
      $embargo = NULL;
      if (isset($values->embargoes_log_embargo)) {
        $embargo = $this->embargoesStorage->load($values->embargoes_log_embargo);
      }
      if (!$embargo) {
        // XXX: Create a dummy embargo so we can load appropriate actions as
        // best we can. This embargo will not be saved.
        $embargo = $this->embargoesStorage->create();
      }
      $map = $this->embargoes->getNotificationStatusesAsFormOptions($embargo);
      if (isset($map[$value])) {
        $value = $map[$value];
      }
    }
    return $this->sanitizeValue($value);
  }
}
