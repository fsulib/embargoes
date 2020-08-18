<?php

namespace Drupal\embargoes\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\ResettableStackedRouteMatchInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\Cache;
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

  /**
   * {@inheritdoc}
   */
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

  /**
   * Construct embargo notification block.
   *
   * @param array $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\ResettableStackedRouteMatchInterface $route_match
   *   A route matching interface.
   * @param Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A configuration factory interface.
   * @param \Drupal\embargoes\EmbargoesEmbargoesServiceInterface $embargoes
   *   An embargoes management service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   An entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ResettableStackedRouteMatchInterface $route_match, ConfigFactoryInterface $config_factory, EmbargoesEmbargoesServiceInterface $embargoes, EntityTypeManagerInterface $entity_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->adminMail = $config_factory->get('embargoes.settings')->get('embargo_contact_email');
    $this->notificationMessage = $config_factory->get('embargoes.settings')->get('embargo_notification_message');
    $this->entityManager = $entity_manager;
    $this->embargoes = $embargoes;
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      $embargoes = $this->embargoes->getCurrentEmbargoesByNids([$node->id()]);
      $num_embargoes = count($embargoes);

      if ($num_embargoes > 0) {
        $t = $this->getStringTranslation();
        $embargoes_info = [];
        $cache_tags = [
          "node:{$node->id()}",
          "extensions",
          "env",
        ];
        $embargoes_count = $t->formatPlural(
          $num_embargoes,
          'This resource is under 1 embargo:',
          'This resource is under @count embargoes:'
        );

        $contact_message = "";
        foreach ($embargoes as $embargo_id) {

          $embargo = $this->entityManager->getStorage('embargoes_embargo_entity')->load($embargo_id);
          $embargo_info = [];

          // Expiration string.
          if (!$embargo->getExpirationType()) {
            $embargo_info['expiration'] = $t->translate('Indefinitely');
            $embargo_info['has_duration'] = FALSE;
          }
          else {
            $embargo_info['expiration'] = $t->translate('Expires @duration', [
              '@duration' => $embargo->getExpirationDate(),
            ]);
            $embargo_info['has_duration'] = TRUE;
          }

          // Embargo type string, including a message for the given type.
          if (!$embargo->getEmbargoType()) {
            $embargo_info['type'] = 'Files';
            $embargo_info['type_message'] = $t->translate('Access to all associated files of this resource is restricted');
          }
          else {
            $embargo_info['type'] = 'Node';
            $embargo_info['type_message'] = $t->translate('Access to this resource and all associated files is restricted');
          }

          // Exempt IP string.
          if (!($embargo->getExemptIps())) {
            $embargo_info['exempt_ips'] = '';
          }
          else {
            $embargo_info['exempt_ips'] = $t->translate('Allowed Networks: @network', [
              '@network' => $this->entityManager->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps())->label(),
            ]);
          }

          // Determine if given user is exempt or not. If not, prepare a message
          // the user can use to request access.
          $exempt_users = $embargo->getExemptUsers();
          $embargo_info['user_exempt'] = FALSE;
          foreach ($exempt_users as $user) {
            if ($user['target_id'] == \Drupal::currentUser()->id()) {
              $embargo_info['user_exempt'] = TRUE;
            }
          }
          if (!$embargo_info['user_exempt']) {
            $contact_message = $t->translate(
              $this->notificationMessage,
              ['@contact' => $this->adminMail]
            );
          }

          $embargo_info['dom_id'] = Html::getUniqueId('embargo_notification');
          $embargoes_info[] = $embargo_info;

          array_push(
            $cache_tags,
            "config:embargoes.embargoes_embargo_entity.{$embargo->id()}"
          );

        }

        return [
          '#theme' => 'embargoes_notifications',
          '#count' => $embargoes_count,
          '#message' => $contact_message,
          '#embargo_info' => $embargoes_info,
          '#cache' => [
            'tags' => $cache_tags,
          ],
        ];
      }
    }

    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // When the given node changes (route), the block should rebuild.
    if ($node = \Drupal::routeMatch()->getParameter('node')) {
      return Cache::mergeTags(
        parent::getCacheTags(),
        array('node:' . $node->id())
      );
    }

    // Return default tags, if not on a node page.
    return parent::getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Ensure that with every new node/route, this block will be rebuilt.
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }

}
