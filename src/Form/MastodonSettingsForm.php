<?php

namespace Drupal\mastodon\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Colorfield\Mastodon\MastodonOAuth;

/**
 * Class MastodonSettingsForm.
 *
 * @todo create and use a oAuth service.
 */
class MastodonSettingsForm extends ConfigFormBase {

  /**
   * Authorization URL from the Mastodon instance.
   *
   * @var string
   */
  private $authorizationUrl;

  /**
   * Initializes the Mastodon application.
   *
   * As a side effect, gets the client_id and client_name.
   */
  private function initializeApplication() {
    $config = $this->config('mastodon.settings');
    $oAuth = new MastodonOAuth(
      $config->get('application_name'),
      $config->get('mastodon_instance')
    );
    $this->authorizationUrl = $oAuth->getAuthorizationUrl();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mastodon.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mastodon_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mastodon.settings');
    $form['mastodon_instance'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mastodon instance'),
      '#description' => $this->t('Mastodon instance domain without the protocol (mastodon.social)'),
      '#maxlength' => 200,
      '#size' => 64,
      '#default_value' => $config->get('mastodon_instance'),
    ];
    $form['application_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application name'),
      '#description' => $this->t('Your application name.'),
      '#maxlength' => 80,
      '#size' => 64,
      '#default_value' => $config->get('application_name'),
    ];
    $form['scopes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Scopes'),
      '#description' => $this->t('Application scopes.'),
      '#options' => [
        'read' => $this->t('read'),
        'write' => $this->t('write'),
        'follow' => $this->t('follow'),
      ],
      '#default_value' => $config->get('scopes'),
    ];
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client id'),
      '#description' => $this->t('@todo'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('client_id'),
    ];
    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client secret'),
      '#description' => $this->t('@todo'),
      '#maxlength' => 64,
      '#enabled' => FALSE,
      '#size' => 64,
      '#default_value' => $config->get('client_secret'),
    ];
    $form['bearer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer'),
      '#description' => $this->t('@todo'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $config->get('bearer'),
    ];
    $form['#attached']['library'][] = 'mastodon/mastodon.admin';
    $form['#attached']['drupalSettings']['mastodon'] = [
      'mastodon_instance' => $config->get('mastodon_instance'),
      'application_name' => $config->get('application_name'),
      // @todo add values
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('mastodon.settings')
      ->set('mastodon_instance', $form_state->getValue('mastodon_instance'))
      ->set('application_name', $form_state->getValue('application_name'))
      ->set('scopes', $form_state->getValue('scopes'))
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('client_secret', $form_state->getValue('client_secret'))
      ->set('bearer', $form_state->getValue('bearer'))
      ->save();
  }

}
