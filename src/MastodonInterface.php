<?php

namespace Drupal\mastodon;

/**
 * Interface MastodonInterface.
 */
interface MastodonInterface {

  /**
   * Authenticates a user on a Mastodon instance.
   *
   * Based on verify_credentials API endpoint.
   *
   * @param string $email
   *   Email address.
   * @param string $password
   *   Password.
   *
   * @return \Colorfield\Mastodon\UserVO
   *   User value object.
   */
  public function authenticateUser($email, $password);

  /**
   * Getter for the MastodonAPI object.
   *
   * @return \Colorfield\Mastodon\MastodonAPI
   *   Mastodon API.
   */
  public function getApi();

}
