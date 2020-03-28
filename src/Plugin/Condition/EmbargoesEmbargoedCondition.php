<?php

namespace Drupal\embargoes\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;

/**
 * Provides a 'Node is embargoed (embargoes)' condition to enable a condition based in module selected status.
 *
 * @Condition(
 *   id = "embargoes_embargoed_condition",
 *   label = @Translation("Node is embargoed"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", required = TRUE , label = @Translation("Node"))
 *   }
 * )
 *
 */
class EmbargoesEmbargoedCondition extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return  parent::defaultConfiguration();
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $embargoed = \Drupal::service('embargoes.embargoes')->getEmbargoesByNode($node->id());
    }
    else {
      $embargoed = FALSE;
    }
    return $embargoed;
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
  }

}
