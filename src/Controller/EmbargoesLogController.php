<?php

namespace Drupal\embargoes\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\examples\Utility\DescriptionTemplateTrait;

class EmbargoesLogController extends ControllerBase {

  public function showRenderedLog() {

    $rows = [
      ['Now', 'Me', '1', 'Created', '1'],
    ];

    $table = [
      '#type' => 'table',
      '#header' => ['Time', 'User', 'Node', 'Action', 'Embargo'],
      '#rows' => $rows,
    ];

    return [
      '#type' => '#markup',
      '#markup' => $table,
    ];

  }

}
