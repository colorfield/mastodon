<?php

namespace Drupal\mastodon;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Provides an interface defining a Mastodon entity.
 */
interface MastodonEntityInterface extends ContentEntityInterface {

  /**
   * Gets the Mastodon entity identifier.
   *
   * @return string|int|null
   *   The Mastodon entity identifier, or NULL if the object
   *   does not yet have a Mastodon identifier.
   */
  public function mastodonId();

}
