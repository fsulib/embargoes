<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;

/**
 * Class EmbargoesLogController.
 */
class EmbargoesIpAccessDeniedController extends ControllerBase {

  public function response() {

    $allowed_ranges = explode('.', $_GET['ranges']);
    $host = \Drupal::request()->getSchemeAndHttpHost();
    $path = $_GET['path'];
    $requested_resource = $host . $path;
    $contact_email = \Drupal::config('embargoes.settings')->get('embargo_contact_email');

    $message = "<p>Your request for the following resource could not be resolved:<br/><strong>{$requested_resource}</strong></p><br/>";
    $message .= "<p>Access to this resource is restricted to the following networks:<br/><ul>";
    foreach ($allowed_ranges as $allowed_range) {
      $allowed_range_entity = \Drupal::entityTypeManager()->getStorage('embargoes_ip_range_entity')->load($allowed_range);
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
      '#cache' => array("max-age" => 0),
    ];
  }

}
