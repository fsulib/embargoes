<?php

namespace Drupal\embargoes\Access;

use Drupal\embargoes\EmbargoesEmbargoesServiceInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use InvalidArgumentException;

/**
 * Base implementation of embargoed access.
 */
abstract class EmbargoedAccessResult implements EmbargoedAccessInterface {

  /**
   * An embargoes service.
   *
   * @var \Drupal\embargoes\EmbargoesEmbargoesServiceInterface
   */
  protected $embargoes;

  /**
   * The request object.
   *
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * An entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * A config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * A Drupal messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * String translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translator;

  /**
   * URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructor for access control managers.
   *
   * @param \Drupal\embargoes\EmbargoesEmbargoesServiceInterface $embargoes
   *   An embargoes service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request being made to check access against.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   An entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   A configuration object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   A Drupal messenger.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   A string translation manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   A URL generator.
   */
  public function __construct(EmbargoesEmbargoesServiceInterface $embargoes, RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config, MessengerInterface $messenger, TranslationInterface $translator, UrlGeneratorInterface $url_generator) {
    $this->embargoes = $embargoes;
    $this->request = $request_stack->getCurrentRequest();
    $this->entityTypeManager = $entity_type_manager;
    $this->config = $config;
    $this->messenger = $messenger;
    $this->translator = $translator;
    $this->urlGenerator = $url_generator;
  }

  /**
   * Return the type of entity this should apply to.
   *
   * @return string
   *   The entity type this access control should apply to.
   */
  abstract public static function entityType();

  /**
   * {@inheritdoc}
   */
  public function isActivelyEmbargoed(EntityInterface $entity, AccountInterface $user) {
    $entity_type = $entity->getEntityType()->id();
    $expected = static::entityType();
    if ($entity_type !== $expected) {
      throw new InvalidArgumentException($this->translator->translate('Attempting to check embargoed access status for an entity of type %type (expected: %expected)', [
        '%type' => $entity_type,
        '%expected' => $expected,
      ]));
    }
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function setEmbargoMessage(EntityInterface $entity) {
    $embargoes = $this->embargoes->getCurrentEmbargoesByNids([$entity->id()]);
    if ($this->shouldSetEmbargoMessage() && !empty($embargoes)) {
      // Warnings to pop.
      $messages = [
        $this->translator->formatPlural(count($embargoes), 'This resource is under 1 embargo', 'This resource is under @count embargoes'),
      ];
      // Pop additional warnings per embargo.
      foreach ($embargoes as $embargo_id) {
        $embargo = $this->entityTypeManager
          ->getStorage('embargoes_embargo_entity')
          ->load($embargo_id);
        if ($embargo) {
          // Custom built message from three conditions: are nodes or files
          // embargoed, are networks exempt, and does it expire?
          $type = $embargo->getEmbargoType();
          $ip_range = $embargo->getExemptIps() ?
            $this->entityTypeManager->getStorage('embargoes_ip_range_entity')->load($embargo->getExemptIps()) :
            NULL;
          $expiration = $embargo->getExpirationType();
          $expiration_date = $expiration ? $embargo->getExpirationDate() : '';
          $args = [
            '%date' => $expiration_date,
            '%ip_range' => $ip_range->label(),
          ];
          // Determine a message to set.
          if (!$type && is_null($ip_range) && !$expiration) {
            $messages[] = $this->translator->translate('- Access to all associated files of this resource is restricted indefinitely.');
          }
          elseif (!$type && is_null($ip_range) && $expiration) {
            $messages[] = $this->translator->translate('- Access to all associated files of this resource is restricted until %date.', $args);
          }
          elseif (!$type && !is_null($ip_range) && !$expiration) {
            $messages[] = $this->translator->translate('- Access to all associated files of this resource is restricted to the %ip_range network indefinitely.', $args);
          }
          elseif (!$type && !is_null($ip_range) && $expiration) {
            $messages[] = $this->translator->translate('- Access to all associated files of this resource is restricted to the %ip_range network until %date.', $args);
          }
          elseif ($type && is_null($ip_range) && !$expiration) {
            $messages[] = $this->translator->translate('- Access to this resource and all associated resources is restricted indefinitely.');
          }
          elseif ($type && is_null($ip_range) && $expiration) {
            $messages[] = $this->translator->translate('- Access to this resource and all associated resources is restricted until %date.', $args);
          }
          elseif ($type && !is_null($ip_range) && !$expiration) {
            $messages[] = $this->translator->translate('- Access to this resource and all associated resources is restricted to the %ip_range network indefinitely.', $args);
          }
          else {
            $messages[] = $this->translator->translate('- Access to this resource and all associated resources is restricted to the %ip_range network until %date.', $args);
          }
        }
      }
      foreach ($messages as $message) {
        $this->messenger->addWarning($message);
      }
    }
  }

  /**
   * Helper to determine if the embargo message should be set.
   *
   * @return bool
   *   TRUE or FALSE depending on whether an embargo message should be set.
   */
  protected function shouldSetEmbargoMessage() {
    $show_embargo_message = $this->config
      ->get('embargoes.settings')
      ->get('show_embargo_message');
    return (bool) $show_embargo_message;
  }

  /**
   * {@inheritdoc}
   */
  public function getIpEmbargoRedirectUrl(EntityInterface $entity, AccountInterface $user) {
    return $this->urlGenerator->generateFromRoute('embargoes.ip_access_denied', [
      'query' => [
        'path' => $this->request->getRequestUri(),
        'ranges' => [],
      ],
    ]);
  }

}
