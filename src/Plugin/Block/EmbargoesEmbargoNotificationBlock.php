<?php

namespace Drupal\embargoes\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a "Embargo Notifications" block.
 *
 * @Block(
 *   id="embargoes_embargo_notification_block",
 *   admin_label = @Translation("Embargo Notifications"),
 *   category = @Translation("Embargoes")
 * )
 */
class EmbargoesEmbargoNotificationBlock extends BlockBase implements ContainerFactoryPluginInterface {

//  /**
//   * An entity type manager.
//   *
//   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
//   */
//  protected $entityManager;

  /**
   * The admin email address.
   *
   * @var string
   */
  protected $adminMail;

  /**
   * The notification message.
   *
   * @var string
   */
  protected $notificationMessage;

  /**
   * An entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * A route matching interface.
   *
   * @var \Drupal\Core\Routing\ResettableStackedRouteMatchInterface
   */
  protected $routeMatch;

  /**
   * An embargoes service.
   *
   * @var \Drupal\embargoes\EmbargoesEmbargoesServiceInterface
   */
  protected $embargoes;

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('config.factory'),
      $container->get('embargoes.embargoes'),
      $container->get('entity_type.manager')
    );
  }

//  /**
//   * {@inheritdoc}
//   */
//  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
//    return new static(
//      $configuration,
//      $plugin_id,
//      $plugin_definition,
//      $container->get('current_route_match'),
//      $container->get('embargoes.embargoes'),
//      $container->get('entity_type.manager'));
//  }

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ResettableStackedRouteMatchInterface $route_match, ConfigFactoryInterface $config_factory, EmbargoesEmbargoesServiceInterface $embargoes, EntityTypeManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->adminMail = $config_factory->get('embargoes.settings')->get('embargo_contact_email');
    $this->notificationMessage = $config_factory->get('embargoes.settings')->get('embargo_notification_message');
    $this->entityManager = $entity_manager;
    $this->embargoes = $embargoes;
    $this->routeMatch = $route_match;
  }
//  /**
//   * Constructs an embargoes policies block.
//   *
//   * @param array $configuration
//   *   Block configuration.
//   * @param string $plugin_id
//   *   The plugin ID.
//   * @param mixed $plugin_definition
//   *   The plugin definition.
//   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
//   *   A route matching interface.
//   * @param \Drupal\embargoes\EmbargoesEmbargoesServiceInterface $embargoes
//   *   An embargoes management service.
//   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
//   *   An entity type manager.
//   */
//  public function __construct(array $configuration, $plugin_id, $plugin_definition, ResettableStackedRouteMatchInterface $route_match, EmbargoesEmbargoesServiceInterface $embargoes, EntityTypeManagerInterface $entity_manager) {
//    parent::__construct($configuration, $plugin_id, $plugin_definition);
//    $this->routeMatch = $route_match;
//    $this->embargoes = $embargoes;
//    $this->entityManager = $entity_manager;
//  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      $embargoes = $this->embargoes->getCurrentEmbargoesByNids([$node->id()]);

      $num_embargoes = count($embargoes);
      ksm($num_embargoes);
      if ($num_embargoes > 0) {
        $t = $this->getStringTranslation();
        $embargoes_info = [];
        $cache_tags = [
          "node:{$node->id()}",
        ];
        $embargoes_count = $t->formatPlural(
          $num_embargoes,
          'This resource is under 1 embargo:',
          'This resource is under @count embargoes:'
        );
//
        foreach ($embargoes as $embargo_id) {
          ksm($embargoes);
          $embargo = $this->entityManager->getStorage('embargoes_embargo_entity')->load($embargo_id);
          $embargo_info = [];
          // Expiration string.
//          if (!$embargo->getExpirationType()) {
//            $embargo_info['expiration'] = $t->translate('Duration: Indefinite');
//          }
//          else {
//            $embargo_info['expiration'] = $t->translate('Duration: Until @duration', [
//              '@duration' => $embargo->getExpirationDate(),
//            ]);
//          }
//          // Embargo type string.
//          if (!$embargo->getEmbargoType()) {
//            $embargo_info['type'] = $t->translate('Disallow Access To: Resource Files');
//          }
//          else {
//            $embargo_info['type'] = $t->translate('Disallow Access To: Resource');
//          }
//          // Exempt IP string.
//          if (!($embargo->getExemptIps())) {
//            $embargo_info['exempt_ips'] = '';
//          }
//          else {
//            $embargo_info['exempt_ips'] = $t->translate('Allowed Networks: @network', [
//              '@network' => $this->entityManager->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps())->label(),
//            ]);
//          }
          $embargo_info['type'] = $t->translate('embargo info');
          $embargo_info['message'] = $this->notificationMessage;
          $embargoes_info[] = $embargo_info;

          $cache_tags[] = "embargoes_embargo_entity:{$embargo->id()}";
        }
//
        return [
          '#theme' => 'embargoes_notifications',
          '#count' => $t->translate('Test count'),
          '#embargo_info' => $embargoes_info,
          '#cache' => [
            'tags' => $cache_tags,
          ],
        ];
      }
    }

    return [];
  }

}
