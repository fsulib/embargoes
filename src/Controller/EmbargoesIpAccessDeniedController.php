<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EmbargoesLogController.
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
   * Helper function to attempt to get the current request.
   *
   * @return string|null
   *   The requested resource, or NULL if there is no current request.
   */
  protected function getRequestedResource() {
    if (!is_null($this->request)) {
      $path = $this->request->query->get('path');
      $host = $this->request->getSchemeAndHttpHost();
      return "{$host}{$path}";
    }
  }

  /**
   * Formats a response for an IP access denied page.
   *
   * @return array
   *   Renderable array of markup for IP access denied.
   */
  public function response() {
    $requested_resource = $this->getRequestedResource();
    $contact_email = $this->config('embargoes.settings')->get('embargo_contact_email');

    $message = "<p>Your request for the following resource could not be resolved:<br/><strong>{$requested_resource}</strong></p><br/>";
    $message .= "<p>Access to this resource is restricted to the following networks:<br/><ul>";
    foreach ($this->request->query->get('ranges') as $allowed_range) {
      $allowed_range_entity = $this->entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($allowed_range);
      if ($allowed_range_entity->getProxyUrl() != '') {
        $message .= "<li><a href='{$allowed_range_entity->getProxyUrl()}{$requested_resource}'>{$allowed_range_entity->label()}</a></li>";
      }
      else {
        $message .= "<li>{$allowed_range_entity->label()}</li>";
      }
    }
    $message .= "</ul></p>";
    $message .= "<p>If any of the listed networks above appear as links, you may be able to reach the resource by authenticating through a proxy.</p>";
    if ($contact_email != '') {
      $message .= "<p>If you have any questions about access to this resource, contact <a href='mailto:{$contact_email}'>{$contact_email}</a> for more information.</p>";
    }

    return [
      '#type' => 'markup',
      '#markup' => render($message),
      '#cache' => ["max-age" => 0],
    ];
  }

}
