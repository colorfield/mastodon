<?php

namespace Drupal\mastodon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Colorfield\Mastodon\MastodonOAuth;

/**
 * Class MastodonOAuthController.
 */
class MastodonOAuthController extends ControllerBase {

  /**
   * Gets the authorization URL.
   *
   * @param string $name
   *   The application name.
   * @param string $instance
   *   The Mastodon instance.
   *
   * @return string
   *   Authorization URL.
   */
  private function prepareAuthorizationUrl($name, $instance) {
    $oAuth = new MastodonOAuth($name, $instance);
    return $oAuth->getAuthorizationUrl();
  }

  /**
   * Outputs the authorization URL in JSON.
   *
   * @return string
   *   JSON encoded authorization URL
   */
  public function getAuthorizationUrl(Request $request) {
    $name = $request->query->get('application_name');
    $instance = $request->query->get('mastodon_instance');
    $url = $this->prepareAuthorizationUrl($name, $instance);
    $response['authorization_url'] = $url;
    return new JsonResponse($response);
  }

  /**
   * Gets the access token.
   *
   * @return string
   *   The access token
   */
  public function getAccessToken(Request $request) {
    // @todo implement
    $response = '@todo implement';
    return new JsonResponse($response);
  }

}
