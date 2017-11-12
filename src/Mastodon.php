<?php

namespace Drupal\mastodon;

use Colorfield\Mastodon\MastodonOAuth;
use Colorfield\Mastodon\MastodonAPI;
use Colorfield\Mastodon\UserVO;

/**
 * Class Mastodon.
 *
 * @todo type responses
 * @todo set user authentication when needed
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
   * Returns an idea for a user name.
   *
   * @param string $account_name
   *   The user account name, with the @.
   * @param bool $local
   *   Limit search to local accounts.
   *
   * @return int|array|null[
   *   The user id, an array of id's or null if not found.
   */
  public function accountUserId($account_name, $local = TRUE) {
    $result = NULL;
    $params = [
      'q' => $account_name,
    ];
    $searchResults = $this->accountSearch($params);
    // Exclude the accounts that are not coming from this instance.
    if ($local) {
      $searchResultIndex = 0;
      foreach ($searchResults as $searchResult) {
        // If the acct contains an @ it is from another instance.
        if ((strpos($searchResult['acct'], '@') !== FALSE)) {
          unset($searchResults[$searchResultIndex]);
        }
        ++$searchResultIndex;
      }
    }
    // If only one match, return the user id.
    if (count($searchResults) === 1) {
      $result = (int) $searchResults[0]['id'];
      // Otherwise return a key value array (user id, acct)
    }
    else {
      $result = [];
      foreach ($searchResults as $searchResult) {
        $result[(int) $searchResult['id']] = $searchResult['acct'];
      }
    }
    return $result;
  }

  /**
   * Fetches an account.
   *
   * @param int $user_id
   *   Mastodon user id.
   *
   * @return array
   *   Account
   */
  public function account($user_id) {
    return $this->api->get('/accounts/' . $user_id);
  }

  /**
   * Gets an account's followers.
   *
   * @param int $user_id
   *   Mastodon user id.
   * @param array $params
   *   Optional parameters: max_id, since_id, limit.
   *
   * @return array
   *   Array of Accounts.
   */
  public function accountFollowers($user_id, array $params = []) {
    return $this->api->get('/accounts/' . $user_id . '/followers', $params);
  }

  /**
   * Gets who is following an account.
   *
   * @param int $user_id
   *   Mastodon user id.
   * @param array $params
   *   Optional parameters: max_id, since_id, limit.
   *
   * @return array
   *   Array of Accounts.
   */
  public function accountFollowing($user_id, array $params = []) {
    return $this->api->get('/accounts/' . $user_id . '/following', $params);
  }

  /**
   * Gets an account's statuses.
   *
   * @param int $user_id
   *   Mastodon user id.
   * @param array $params
   *   Optional parameters: only_media, exclude_replies, max_id,
   *   since_id, limit.
   *
   * @return array
   *   Array of Statuses.
   */
  public function accountStatuses($user_id, array $params = []) {
    return $this->api->get('/accounts/' . $user_id . '/statuses', $params);
  }

  /**
   * Gets an account's relationships.
   *
   * @param array $user_ids
   *   Array of user ids.
   *
   * @return array
   *   Array of Relationships of the current user.
   */
  public function accountRelationships(array $user_ids) {
    return $this->api->get('/accounts/relationships', $user_ids);
  }

  /**
   * Searches for accounts.
   *
   * @param array $params
   *   Mandatory: q (what to search for), optional: limit.
   *
   * @return array
   *   Array of matching Accounts.
   */
  public function accountSearch(array $params) {
    return $this->api->get('/accounts/search', $params);
  }

  /**
   * Searches for content.
   *
   * @param array $params
   *   Mandatory: q (what to search for),
   *   optional:resolve (non-local accounts).
   *
   * @return array
   *   Array of matching Results.
   */
  public function contentSearch(array $params) {
    return $this->api->get('/search', $params);
  }

  /**
   * Retrieves a home timeline.
   *
   * Requires authentication.
   *
   * @param array $params
   *   Optional: local, max_id, since_id, limit (max 40).
   *
   * @return array
   *   Array of Statuses, most recent ones first.
   */
  public function timelineHome(array $params = []) {
    return $this->api->get('/timelines/home', $params);
  }

  /**
   * Retrieves a public timeline.
   *
   * Does not require authentication.
   *
   * @param array $params
   *   Optional: local, max_id, since_id, limit (max 40).
   *
   * @return array
   *   Array of Statuses, most recent ones first.
   */
  public function timelinePublic(array $params = []) {
    return $this->api->get('/timelines/public', $params);
  }

  /**
   * Retrieves a timeline by hashtag.
   *
   * Does not require authentication.
   *
   * @param array $params
   *   Optional: local, max_id, since_id, limit (max 40).
   *
   * @return array
   *   Array of Statuses, most recent ones first.
   */
  public function timelineTag($tag, array $params = []) {
    return $this->api->get('/timelines/tag/' . $tag, $params);
  }

}
