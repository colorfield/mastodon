<?php

namespace Drupal\mastodon\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Colorfield\Mastodon\MastodonOAuth;

/**
 * Class MastodonOAuthController.
 *
 * To be used by the Javascript implementation for progressive enhancement.
 */
class MastodonOAuthController extends ControllerBase {

  /**
   * Outputs the authorization URL in JSON.
   *
   * @return string
   *   JSON encoded authorization URL
   */
  public function getAuthorizationUrl(Request $request) {
    $name = $request->query->get('application_name');
    $instance = $request->query->get('mastodon_instance');
    $oAuth = new MastodonOAuth($name, $instance);
    $url = $oAuth->getAuthorizationUrl();
    $response['client_id'] = $oAuth->config->getClientId();
    $response['client_secret'] = $oAuth->config->getClientSecret();
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
