<?php

namespace Drupal\embargoes\Plugin\Condition;

use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Session\AccountInterface;
use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Condition to filter on whether or not a node is embargoed.
 *
 * @Condition(
 *   id = "embargoes_embargoed_condition",
 *   label = @Translation("Node is embargoed"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", required = TRUE , label = @Translation("Node"))
 *   }
 * )
 */
class EmbargoesEmbargoedCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * A route matching interface.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * An embargoes service.
   *
   * @var \Drupal\embargoes\EmbargoesEmbargoesServiceInterface
   */
  protected $embargoes;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('embargoes.embargoes'),
      $container->get('current_user'),
      $container->get('request_stack'));
  }

  /**
   * Create a new embargoed condition.
   *
   * @param array $configuration
   *   The condition configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param string $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A route matching interface.
   * @param \Drupal\embargoes\EmbargoesEmbargoesServiceInterface $embargoes
   *   An embargoes service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The current request stack.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EmbargoesEmbargoesServiceInterface $embargoes, AccountInterface $current_user, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
    $this->embargoes = $embargoes;
    $this->currentUser = $current_user;
    $this->request = $request_stack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['filter'] = [
      '#type' => 'radios',
      '#title' => $this->t('Filter'),
      '#default_value' => $this->configuration['filter'],
      '#description' => $this->t('Select the scope of embargo to trigger on.'),
      '#options' => [
        'off' => $this->t('Always trigger regardless of embargo status'),
        'all' => $this->t('All embargoes on node'),
        'current' => $this->t('Current embargoes on node (ignore expired)'),
        'active' => $this->t('Active embargoes on node (ignore bypassed)'),
      ],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['filter'] = $form_state->getValue('filter');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['filter' => 'off'] + parent::defaultConfiguration();
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {

      switch ($this->configuration['filter']) {
        case 'off':
          $embargoed = TRUE;
          break;

        case 'all':
          $embargoed = $this->embargoes->getAllEmbargoesByNids([$node->id()]);
          break;

        case 'current':
          $embargoed = $this->embargoes->getCurrentEmbargoesByNids([$node->id()]);
          break;

        case 'active':
          $embargoed = $this->embargoes->getActiveEmbargoesByNids([$node->id()], $this->request->getClientIp(), $this->currentUser);
          break;
      }

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
