<?php

/**
 * Implements views integration for embargo logs.
 */

/**
 * Implements hook_views_data().
 */
function embargoes_log_views_views_data() {
  // Embargoes log table view.
  $data = [
    'embargoes_log' => [
      'table' => [
        'group' => t('Embargoes logs'),
        'provider' => 'embargoes',
        'wizard_id' => 'embargoes_log_data',
        'base' => [
          'field' => 'id',
          'title' => t('Embargoes log entries'),
          'help' => t('Contains a list of embargoes logs.'),
        ],
      ],
    ],
  ];

  // Implement individual columns.
  $data['embargoes_log']['id'] = [
    'title' => t('Log ID'),
    'help' => t('The ID of the log in the database.'),
    'field' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];
  $data['embargoes_log']['time'] = [
    'title' => t('Timestamp'),
    'help' => t('The time the log entry was created.'),
    'field' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
    'filter' => [
      'id' => 'date',
    ],
  ];
  $data['embargoes_log']['action'] = [
    'title' => t('Action Taken'),
    'help' => t('The action that was taken for the log entry.'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'embargoes_log_status',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];
  $data['embargoes_log']['node'] = [
    'title' => t('Embargoed Node'),
    'help' => t('The node this embargo log applies to.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'base' => 'node_field_data',
      'base field' => 'nid',
      'id' => 'standard',
      'label' => t('Embargoed node'),
    ],
  ];
  $data['embargoes_log']['uid'] = [
    'title' => t('UID'),
    'help' => t('The user that this log was created by.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'relationship' => [
      'base' => 'users_field_data',
      'base field' => 'uid',
      'id' => 'standard',
      'title' => t('Log Creator'),
    ],
  ];
  $data['embargoes_log']['embargo'] = [
    'title' => t('Embargo ID'),
    'help' => t('The ID of the embargo this log applies to.'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  return $data;
}
