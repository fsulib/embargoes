<?php

namespace Drupal\embargoes\Plugin\ContextReaction;

use Drupal\Core\Form\FormStateInterface;
use Drupal\context\ContextReactionPluginBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @ContextReaction(
 *   id = "embargoes_calculate_embargo_access_restrictions_reaction",
 *   label = @Translation("Calculate embargo access restrictions")
 * )
 */
class EmbargoesCalculateEmbargoAccessRestrictionsReaction extends ContextReactionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Calculate embargo access restrictions.');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    throw new AccessDeniedHttpException();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
