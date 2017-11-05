<?php

namespace Drupal\mastodon;

use Colorfield\Mastodon\MastodonOAuth;
use Colorfield\Mastodon\MastodonAPI;
use Colorfield\Mastodon\UserVO;

/**
 * Class Mastodon.
 */
class Mastodon implements MastodonInterface {

  /**
   * MastodonOAuth definition.
   *
   * @var \Colorfield\Mastodon\MastodonOAuth
   */
  private $oAuth;

  /**
   * MastodonAPI definition.
   *
   * @var \Colorfield\Mastodon\MastodonAPI
   */
  private $api;

  /**
   * Constructs a new Mastodon object based on the configuration.
   */
  public function __construct() {
    // @todo DI of config.factory
    $mastodonConfig = \Drupal::config('mastodon.settings');
    $auth = [];
    $auth['name'] = $mastodonConfig->get('application_name');
    $auth['instance'] = $mastodonConfig->get('mastodon_instance');
    $auth['client_id'] = $mastodonConfig->get('client_id');
    $auth['client_secret'] = $mastodonConfig->get('client_secret');
    $auth['bearer'] = $mastodonConfig->get('bearer');
    // @todo website
    // $mastodonConfig['website'] = '';
    foreach ($auth as $key => $config) {
      if (empty($config)) {
        drupal_set_message(
          t('Missing Mastodon OAuth configuration for @config',
            ['@config' => $key]
          ), 'error'
        );
      }
    }
    $this->oAuth = new MastodonOAuth($auth['name'], $auth['instance']);
    $this->oAuth->config->setClientId($auth['client_id']);
    $this->oAuth->config->setClientSecret($auth['client_secret']);
    $this->oAuth->config->setBearer($auth['bearer']);
    $this->api = new MastodonAPI($this->oAuth->config);
  }

  /**
   * {@inheritdoc}
   */
  public function getApi() {
    return $this->api;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticateUser($email, $password) {
    $this->oAuth->authenticateUser($email, $password);
    $credentials = $this->api->get('/accounts/verify_credentials');
    $user = new UserVO($credentials);
    return $user;
  }

  /**
   * Gets followers for a Mastodon user.
   *
   * @param int $user_id
   *   Mastodon user id.
   * @param array $params
   *   Optional parameters: max_id, since_id, limit.
   *
   * @return array
   *   Array of accounts.
   */
  public function getFollowers($user_id, array $params = []) {
    return $this->api->get('/accounts/1/followers', $params);
  }

}
