<?php

namespace Drupal\embargoes\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EmbargoesSettingsForm.
 */
class EmbargoesNotificationsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'embargoes_notifications';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['embargoes.notifications'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('embargoes.notifications');
    $form = parent::buildForm($form, $form_state);

    $form['creations'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Creation Notifications'),
    ];
    $form['creations']['creations_active'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo creation notification emails'),
      '#default_value' => (!is_null($config->get('creations_active')) ? $config->get('creations_active') : 0),
      '#options' => [
        '0' => $this->t('Disabled'),
        '1' => $this->t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'creations_active',
      ],
    ];
    $form['creations']['creations_from_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Address'),
      '#description' => $this->t('Enter email address that embargo creation notification emails should be sent from'),
      '#default_value' => $config->get('creations_from_address'),
      '#states' => [
        'visible' => [
          ':input[name="creations_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $form['creations']['creations_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo creation notification emails by default'),
      '#default_value' => $config->get('creations_emails'),
      '#states' => [
        'visible' => [
          ':input[name="creations_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $creations_template_default = <<<EOM
Hello [recipient_email_address],

This message is to inform you that an embargo has been placed on the following resource:
- Title: [node_title]
- Link: [node_link]
- Expiration: [expiration_date].

For more details, contact [contact_email_address].
EOM;
    $form['creations']['creations_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Creation notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo creation notifications, using [recipient_email_address], [contact_email_address], [node_title], [node_link], and [expiration_date] as tokens.'),
      '#default_value' => (!is_null($config->get('creations_template')) ? $config->get('creations_template') : $creations_template_default),
      '#states' => [
        'visible' => [
          ':input[name="creations_active"]' => ['value' => '1'],
        ],
      ],
    ];

    $form['updates'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Update Notifications'),
    ];
    $form['updates']['updates_active'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo update notification emails'),
      '#default_value' => (!is_null($config->get('updates_active')) ? $config->get('updates_active') : 0),
      '#options' => [
        '0' => $this->t('Disabled'),
        '1' => $this->t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'updates_active',
      ],
    ];
    $form['updates']['updates_from_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Address'),
      '#description' => $this->t('Enter email address that embargo update notification emails should be sent from'),
      '#default_value' => $config->get('updates_from_address'),
      '#states' => [
        'visible' => [
          ':input[name="updates_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $form['updates']['updates_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo update notification emails by default'),
      '#default_value' => $config->get('updates_emails'),
      '#states' => [
        'visible' => [
          ':input[name="updates_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $updates_template_default = <<<EOM
Hello [recipient_email_address],

This message is to inform you that an embargo has been updated on the following resource:
- Title: [node_title]
- Link: [node_link]
- Expiration: [expiration_date].

For more details, contact [contact_email_address].
EOM;
    $form['updates']['updates_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Update notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo update notifications, using [recipient_email_address], [contact_email_address], [node_title], [node_link], and [expiration_date] as tokens.'),
      '#default_value' => (!is_null($config->get('updates_template')) ? $config->get('updates_template') : $updates_template_default),
      '#states' => [
        'visible' => [
          ':input[name="updates_active"]' => ['value' => '1'],
        ],
      ],
    ];

    $form['deletions'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Deletion Notifications'),
    ];
    $form['deletions']['deletions_active'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo deletion notification emails'),
      '#default_value' => (!is_null($config->get('deletions_active')) ? $config->get('deletions_active') : 0),
      '#options' => [
        '0' => $this->t('Disabled'),
        '1' => $this->t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'deletions_active',
      ],
    ];
    $form['deletions']['deletions_from_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Address'),
      '#description' => $this->t('Enter email address that embargo deletion notification emails should be sent from'),
      '#default_value' => $config->get('deletions_from_address'),
      '#states' => [
        'visible' => [
          ':input[name="deletions_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $form['deletions']['deletions_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo deletion notification emails by default'),
      '#default_value' => $config->get('deletions_emails'),
      '#states' => [
        'visible' => [
          ':input[name="deletions_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $deletions_template_default = <<<EOM
Hello [recipient_email_address],

This message is to inform you that an embargo has been deleted from the following resource:
- Title: [node_title]
- Link: [node_link]

For more details, contact [contact_email_address].
EOM;
    $form['deletions']['deletions_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Deletion notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo deletion notifications, using [recipient_email_address], [contact_email_address], [node_title], and [node_link] as tokens.'),
      '#default_value' => (!is_null($config->get('deletions_template')) ? $config->get('deletions_template') : $deletions_template_default),
      '#states' => [
        'visible' => [
          ':input[name="deletions_active"]' => ['value' => '1'],
        ],
      ],
    ];

    $form['warnings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Expiration Warning Notifications'),
    ];
    $form['warnings']['warnings_active'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo expiration warning notification emails'),
      '#default_value' => (!is_null($config->get('warnings_active')) ? $config->get('warnings_active') : 0),
      '#options' => [
        '0' => $this->t('Disabled'),
        '1' => $this->t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'warnings_active',
      ],
    ];
    $form['warnings']['warnings_period'] = [
      '#type' => 'number',
      '#title' => $this->t('Days until expiry'),
      '#description' => $this->t('Enter the number of days before an embargo expires that an embargo expiration warning notification should be sent'),
      '#default_value' => $config->get('warnings_period'),
      '#min' => 1,
      '#states' => [
        'visible' => [
          ':input[name="warnings_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $form['warnings']['warnings_from_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Address'),
      '#description' => $this->t('Enter email address that embargo warning notification emails should be sent from'),
      '#default_value' => $config->get('warnings_from_address'),
      '#states' => [
        'visible' => [
          ':input[name="warnings_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $form['warnings']['warnings_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration warning notification emails by default'),
      '#default_value' => $config->get('warnings_emails'),
      '#states' => [
        'visible' => [
          ':input[name="warnings_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $warnings_template_default = <<<EOM
Hello [recipient_email_address],

This message is to inform you that an embargo is about to expire in [expiration_warning_period] days on the following resource:
- Title: [node_title]
- Link: [node_link]
- Expiration: [expiration_date].

For more details, contact [contact_email_address].
EOM;
    $form['warnings']['warnings_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Warning notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration warning notifications, using [recipient_email_address], [contact_email_address], [node_title], [node_link], expiration_date] and [expiration_warning_period] as tokens.'),
      '#default_value' => (!is_null($config->get('warnings_template')) ? $config->get('warnings_template') : $warnings_template_default),
      '#states' => [
        'visible' => [
          ':input[name="warnings_active"]' => ['value' => '1'],
        ],
      ],
    ];

    $form['expirations'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Embargo Expiration Notifications'),
    ];
    $form['expirations']['expirations_active'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable embargo expiration notification emails'),
      '#default_value' => (!is_null($config->get('expirations_active')) ? $config->get('expirations_active') : 0),
      '#options' => [
        '0' => $this->t('Disabled'),
        '1' => $this->t('Enabled'),
      ],
      '#attributes' => [
        'name' => 'expirations_active',
      ],
    ];
    $form['expirations']['expirations_from_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('From Address'),
      '#description' => $this->t('Enter email address that embargo expiration notification emails should be sent from'),
      '#default_value' => $config->get('expirations_from_address'),
      '#states' => [
        'visible' => [
          ':input[name="expirations_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $form['expirations']['expirations_emails'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default email recipients'),
      '#description' => $this->t('Enter email addresses that should recieve all embargo expiration notification emails by default'),
      '#default_value' => $config->get('expirations_emails'),
      '#states' => [
        'visible' => [
          ':input[name="expirations_active"]' => ['value' => '1'],
        ],
      ],
    ];
    $expirations_template_default = <<<EOM
Hello [recipient_email_address],

This message is to inform you that an embargo has expired on the following resource:
- Title: [node_title]
- Link: [node_link]
- Expiration: [expiration_date].

For more details, contact [contact_email_address].
EOM;
    $form['expirations']['expirations_template'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Expiration notification email template'),
      '#description' => $this->t('Modify the email template sent for embargo expiration notifications, using [recipient_email_address], [contact_email_address], [node_title], [node_link], and [expiration_date] as tokens.'),
      '#default_value' => (!is_null($config->get('expirations_template')) ? $config->get('expirations_template') : $expirations_template_default),
      '#states' => [
        'visible' => [
          ':input[name="expirations_active"]' => ['value' => '1'],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('embargoes.notifications');

    $config->set('creations_active', $form_state->getValue('creations_active'));
    $config->set('creations_from_address', $form_state->getValue('creations_from_address'));
    $config->set('creations_emails', $form_state->getValue('creations_emails'));
    $config->set('creations_template', $form_state->getValue('creations_template'));

    $config->set('updates_active', $form_state->getValue('updates_active'));
    $config->set('updates_from_address', $form_state->getValue('updates_from_address'));
    $config->set('updates_emails', $form_state->getValue('updates_emails'));
    $config->set('updates_template', $form_state->getValue('updates_template'));

    $config->set('deletions_active', $form_state->getValue('updates_active'));
    $config->set('deletions_from_address', $form_state->getValue('deletions_from_address'));
    $config->set('deletions_emails', $form_state->getValue('updates_emails'));
    $config->set('deletions_template', $form_state->getValue('updates_template'));

    $config->set('warnings_active', $form_state->getValue('warnings_active'));
    $config->set('warnings_period', $form_state->getValue('warnings_period'));
    $config->set('warnings_from_address', $form_state->getValue('warnings_from_address'));
    $config->set('warnings_emails', $form_state->getValue('warnings_emails'));
    $config->set('warnings_template', $form_state->getValue('warnings_template'));

    $config->set('expirations_active', $form_state->getValue('expirations_active'));
    $config->set('expirations_from_address', $form_state->getValue('expirations_from_address'));
    $config->set('expirations_emails', $form_state->getValue('expirations_emails'));
    $config->set('expirations_template', $form_state->getValue('expirations_template'));

    $config->save();
    parent::submitForm($form, $form_state);
  }

}
