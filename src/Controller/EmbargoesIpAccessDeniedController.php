<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for displaying an IP access denied message.
 */
class EmbargoesIpAccessDeniedController extends ControllerBase {

  /**
   * The HTTP request.
   *
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs an IP access denied controller.
   *
   * @param Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function __construct(Request $request = NULL) {
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack')->getCurrentRequest());
  }

  /**
   * Formats a response for an IP access denied page.
   *
   * @return array
   *   Renderable array of markup for IP access denied.
   */
  public function response() {
    $ranges = [];
    foreach ($this->request->query->get('ranges', []) as $allowed_range) {
      $allowed_range_entity = $this->entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($allowed_range);
      $proxy_url = $allowed_range_entity->getProxyUrl() != '' ? $allowed_range_entity->getProxyUrl() : NULL;
      $ranges[] = [
        'proxy_url' => $proxy_url,
        'label' => $allowed_range_entity->label(),
      ];
    }

    return [
      '#theme' => 'embargoes_ip_access_denied',
      '#requested_resource' => $this->request->query->get('label', ''),
      '#ranges' => $ranges,
      '#contact_email' => $this->config('embargoes.settings')->get('embargo_contact_email'),
    ];
  }

}
