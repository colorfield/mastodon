<?php

namespace Drupal\mastodon\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\Config;
use Colorfield\Mastodon\MastodonOAuth;
use Drupal\Core\Url;
use Drupal\mastodon\Mastodon;

/**
 * Class MastodonSettingsForm.
 *
 * Defines Mastodon OAuth credentials.
 *
 * @todo refactoring needed, the current code relies too much on mutations.
 */
class MastodonSettingsForm extends ConfigFormBase {

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
   * Initializes OAuth.
   *
   * As a side effect, gets the client id, client secret
   * and authorization URL.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Form configuration.
   *
   * @return array
   *   OAuth credentials.
   */
  private function initializeOauth(Config $config) {
    $result = [];
    $name = $config->get('application_name');;
    $instance = $config->get('mastodon_instance');
    // @todo set scopes to allow other scopes than the default ones
    // $scopes = $config->get('scopes');
    // @todo validate
    $oAuth = new MastodonOAuth($name, $instance);
    // @todo set website
    // $oAuth->config->setWebsite();
    $result['authorization_url'] = $oAuth->getAuthorizationUrl();
    $result['authorization_code'] = '';
    $result['client_id'] = $oAuth->config->getClientId();
    $result['client_secret'] = $oAuth->config->getClientSecret();
    drupal_set_message(
      $this->t('Step 1. Go to the Authorization URL then copy the obtained code into the <em>Authorization code</em> field then save configuration.')
    );
    return $result;
  }

  /**
   * Gets the bearer from the access token.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Form configuration.
   */
  private function getBearerFromAccessToken(Config $config) {
    $result = NULL;
    $oAuth = new MastodonOAuth(
      $config->get('application_name'),
      $config->get('mastodon_instance')
    );
    // @todo set website
    // $oAuth->config->setWebsite();
    $oAuth->config->setClientId($config->get('client_id'));
    $oAuth->config->setClientSecret($config->get('client_secret'));
    // $oAuth->config->setRedirectUris(); // @todo
    $oAuth->config->setAuthorizationCode($config->get('authorization_code'));
    $oAuth->getAccessToken();
    if (!empty($oAuth->config->getBearer())) {
      drupal_set_message($this->t('Step 2. Getting the bearer, you can now re-save configuration and start using the API.'));
      $result = $oAuth->config->getBearer();
    }
    else {
      drupal_set_message($this->t('Error while getting the access token.'), 'error');
    }
    return $result;
  }

  /**
   * Reconstructs the authorization URL after saving the configuration.
   *
   * @param \Drupal\Core\Config\Config $config
   *   Form configuration.
   *
   * @return string
   *   Absolute authorization URL.
   */
  private function getAuthorizationUrl(Config $config) {
    $name = $config->get('application_name');
    $instance = $config->get('mastodon_instance');
    $clientId = $config->get('client_id');
    $clientSecret = $config->get('client_secret');
    $oAuth = new MastodonOAuth($name, $instance);
    $oAuth->config->setClientId($clientId);
    $oAuth->config->setClientSecret($clientSecret);
    // @todo set website
    // $oAuth->config->setWebsite();
    $result = $oAuth->getAuthorizationUrl();
    return $result;
  }

  /**
   * Testing the API.
   */
  private function testApi() {
    drupal_set_message('Testing the API.');
    $mastodon = \Drupal::service('mastodon.api');
    if ($mastodon instanceof Mastodon) {
      $followers = $mastodon->getApi()->get('/accounts/1/followers', ['limit' => 2]);
      drupal_set_message('Fetch followers of user 1.');
      drupal_set_message(print_r($followers, TRUE));
      // $timeline = $mastodon->timelineTag('mastodon');
      // drupal_set_message(print_r($timeline, TRUE));
      // $account = $mastodon->account(1);
      // drupal_set_message(print_r($account, TRUE));
      // $accountSearch = $mastodon->accountSearch(
      // ['q' => 'gargron', 'limit' => 10]
      // );
      // drupal_set_message('Search account for "gargon" limit to 10 results.');
      // drupal_set_message(print_r($accountSearch, TRUE));
      // $contentSearch = $mastodon->contentSearch(
      // ['q' => '#mastodon', 'limit' => 10]
      // );
      // drupal_set_message('Search content for "#mastodon"');
      // drupal_set_message(print_r($contentSearch, TRUE));.
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mastodon.settings');
    $initialValues = [];
    $bearer = NULL;
    // @todo handle the scopes change case
    // Initialize if client id and secret are not set yet.
    if (empty($config->get('client_id')) || empty($config->get('client_secret'))) {
      $initialValues = $this->initializeOauth($config);
    }
    // When the bearer is empty and the authorization code is set
    // get the bearer from the access token.
    // @todo on the first bearer config save, config does not seem set so it is recalled
    elseif (empty($config->get('bearer')) && !empty($config->get('authorization_code'))) {
      $bearer = $this->getBearerFromAccessToken($config);
    }
    // When the bearer is available, test the API.
    elseif (!empty($config->get('bearer'))) {
      $this->testApi();
    }

    $form['mastodon_instance'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Mastodon instance'),
      '#description' => $this->t('Mastodon instance domain without the protocol (example: mastodon.social)'),
      '#maxlength' => 200,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $config->get('mastodon_instance'),
    ];
    $form['application_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Application name'),
      '#description' => $this->t('Your application name.'),
      '#maxlength' => 80,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $config->get('application_name'),
    ];
    $form['scopes'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Scopes'),
      '#description' => $this->t('Application scopes.'),
      '#options' => [
        'read' => $this->t('Read'),
        'write' => $this->t('Write'),
        'follow' => $this->t('Follow'),
      ],
      '#required' => TRUE,
      '#default_value' => $config->get('scopes'),
    ];
    // @todo encrypt client_id, client_secret and bearer
    // @see https://www.drupal.org/node/2920169
    $form['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client id'),
      '#description' => $this->t('OAuth client id.'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => empty($initialValues) ? $config->get('client_id') : $initialValues['client_id'],
    ];
    $form['client_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client secret'),
      '#description' => $this->t('OAuth client secret.'),
      '#maxlength' => 64,
      '#enabled' => FALSE,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => empty($initialValues) ? $config->get('client_secret') : $initialValues['client_secret'],
    ];
    // Show authorization URL on init or after having saved the configuration.
    if (empty($config->get('authorization_code'))) {
      $url = '';
      if (!empty($initialValues)) {
        $url = $initialValues['authorization_url'];
      }
      elseif ((!empty($config->get('client_id')) && !empty($config->get('client_secret')))) {
        $url = $this->getAuthorizationUrl($config);
      }
      $authorizationUrl = Url::fromUri($url, ['absolute' => TRUE]);
      // $authorizationLink = Link::fromTextAndUrl(
      // 'Get authorization URL',
      // $authorizationUrl
      // );.
      $form['authorization_url'] = [
        '#type' => 'item',
        '#title' => t('Authorization URL'),
        // @todo set link
        // '#markup' => $authorizationLink->toRenderable(),
        '#markup' => $authorizationUrl->toString(),
      ];
    }
    // Disabled if the bearer is set.
    // This is a temporary wrapper.
    // @todo required auth code or bearer depending on the OAuth phase
    $form['authorization_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authorization code'),
      '#description' => $this->t('OAuth authorization code, obtained after access confirmation from the authorization URL.'),
      '#enabled' => empty($config->get('bearer')),
      '#access' => empty($config->get('bearer')),
      '#maxlength' => 64,
      '#size' => 64,
      // '#required' => empty($config->get('bearer')),.
      '#default_value' => empty($initialValues) ? $config->get('authorization_code') : $initialValues['authorization_code'],
    ];
    $form['bearer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer'),
      '#description' => $this->t('Bearer obtained by the access token, after submitting the authorization code.'),
      '#enabled' => !empty($config->get('authorization_code')),
      '#access' => !empty($config->get('authorization_code')),
      '#maxlength' => 64,
      '#size' => 64,
      // '#required' => !empty($config->get('authorization_code')),.
      '#default_value' => isset($bearer) ? $bearer : $config->get('bearer'),
    ];
    $form['actions']['reset_oauth_configuration'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Reset OAuth configuration'),
      '#submit' => ['::resetOauthConfiguration'],
    );
    // @todo progressive enhancement via js
    // $form['actions']['get_authorization_url'] = array(
    // '#type' => 'submit',
    // '#value' => $this->t('Get authorization URL'),
    // '#submit' => ['::getAuthorizationUrl'], // js preventDefault
    // );
    // $form['authorization_url'] = [
    // '#type' => 'html_tag',
    // '#tag' => 'p',
    // '#value' => '',
    // ];
    // $form['#attached']['library'][] = 'mastodon/mastodon.admin';
    // $form['#attached']['drupalSettings']['mastodon'] = [
    // 'mastodon_instance' => $config->get('mastodon_instance'),
    // 'application_name' => $config->get('application_name'),
    // // @todo add values
    // ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * Resets the OAuth configuration only, keeps instance, name and scopes.
   */
  public function resetOauthConfiguration(array &$form, FormStateInterface $form_state) {
    $this->config('mastodon.settings')
      ->set('mastodon_instance', $form_state->getValue('mastodon_instance'))
      ->set('application_name', $form_state->getValue('application_name'))
      ->set('scopes', $form_state->getValue('scopes'))
      ->set('client_id', '')
      ->set('client_secret', '')
      ->set('authorization_code', '')
      ->set('bearer', '')
      ->save();
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
      ->set('authorization_code', $form_state->getValue('authorization_code'))
      ->set('bearer', $form_state->getValue('bearer'))
      ->save();
  }

}
