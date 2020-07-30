<?php

namespace Drupal\embargoes_log_views\Plugin\views\wizard;

use Drupal\views\Plugin\views\wizard\WizardPluginBase;

/**
 * Defines a wizard for the embargoes log table.
 *
 * @ViewsWizard(
 *   id = "embargoes_log_data",
 *   module = "embargoes",
 *   base_table = "embargoes_log",
 *   title = @Translation("Embargoes log entries")
 * )
 */
class EmbargoesLog extends WizardPluginBase {

  /**
   * The column for when the embargo was created.
   *
   * @var string
   */
  protected $createdColumn = 'time';

  /**
   * {@inheritdoc}
   */
  protected function defaultDisplayOptions() {
    $options = parent::defaultDisplayOptions();

    $options['access']['type'] = 'perm';
    $options['access']['options']['perm'] = 'access embargoes logs';

    return $options;
  }

}
