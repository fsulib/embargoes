<?php

namespace Drupal\embargoes\Form;

use Drupal\embargoes\EmbargoesIpRangesServiceInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmbargoesIpRangeEntityForm.
 */
class EmbargoesIpRangeEntityForm extends EntityForm {

  /**
   * An embargoes IP ranges manager.
   *
   * @var \Drupal\embargoes\EmbargoesIpRangesServiceInterface
   */
  protected $ipRanges;

  /**
   * Constructor for the IP range entity form.
   *
   * @param \Drupal\embargoes\EmbargoesIpRangesServiceInterface $ip_ranges
   *   An embargoes IP ranges manager.
   */
  public function __construct(EmbargoesIpRangesServiceInterface $ip_ranges) {
    $this->ipRanges = $ip_ranges;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('embargoes.ips'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $range = $this->entity;

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $range->id(),
      '#machine_name' => [
        'exists' => '\Drupal\embargoes\Entity\EmbargoesIpRangeEntity::load',
      ],
      '#disabled' => !$range->isNew(),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $range->label(),
      '#description' => $this->t("Label for the IP range."),
      '#required' => TRUE,
      '#id' => 'ip-range-label',
    ];

    $form['range'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Range'),
      '#maxlength' => 255,
      '#default_value' => implode('|', $range->getRanges()),
      '#description' => $this->t("IP range to be used. Please list in CIDR format, and separate multiple ranges with a '|'."),
      '#required' => TRUE,
      '#element_validate' => [
        '::validateIpRanges',
      ],
    ];

    $form['proxy_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Proxy URL'),
      '#maxlength' => 255,
      '#default_value' => $range->getProxyUrl(),
      '#description' => $this->t("A proxy URL that can be used to gain access to this IP range. This URL will be used to generate a suggested proxy link with the embargoed resource's URL appended, so please include any required parameters."),
    ];

    return $form;
  }

  /**
   * Validates the IP range entered.
   *
   * @param array $element
   *   An array representing the element.
   * @param Drupal\Core\Form\FormStateInterface $form_state
   *   The Drupal form state.
   */
  public function validateIpRanges(array $element, FormStateInterface $form_state) {
    $errors = $this->ipRanges->detectIpRangeStringErrors(array_map('trim', explode('|', trim($form_state->getValue('range')))));
    if (!empty($errors)) {
      $form_state->setError($element, $this->t('Problems detected with the %label IP Range. <br/>Errors: %errors', [
        '%label' => $this->entity->label(),
        '%errors' => implode(", ", $errors),
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $range = $this->entity;
    $range->setRanges($form_state->getValue('range'));
    $range->setProxyUrl($form_state->getValue('proxy_url'));
    $status = $range->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label IP Range.', ['%label' => $range->label()]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label IP Range.', ['%label' => $range->label()]));
    }
    $form_state->setRedirectUrl($range->toUrl('collection'));
  }

}
