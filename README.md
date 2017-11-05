# Mastodon for Drupal 8

## Configuration

Configure OAuth at /admin/config/services/mastodon

1. The configuration form will provide, by default:
a client_id, client_secret and authorization URL.
2. Go to the authorization URL, confirm access, copy
the authorization code and save configuration.
3. Then the form will autocomplete the bearer from the access token,
re-save configuration then.

A test of the API will be displayed, getting followers from
the user 1 of your instance.

## Using the API

The Mastodon class is defined as a Drupal service that uses OAuth
credentials defined via the configuration in the constructor.

Get it via dependency injection ('mastodon.api') then it is ready to use.

```$user = $mastodon->authenticateUser($email, $password);```

Or use the service container when dependency injection is not available.

```
$mastodon = \Drupal::service('mastodon.api');
$user = $mastodon->authenticateUser($email, $password);
```

### Option 1 - rely on the Mastodon wrapper

Currently methods are being implemented as a syntactic sugar
[#2920985](https://www.drupal.org/node/2920985).

```
$user_id = 1;
$followers = $mastodon->getFollowers($user_id, ['limit' => 2]);
```

### Option 2 - use the Mastodon API

You can still use the API endpoints defined via the
[Mastodon API documentation](goo.gl/bQtGNn).

Currently the scopes are defined as read and write (without follow).

```
$user_id = 1;
$followers = $mastodon->getApi()->get('/accounts/'.$user_id.'/followers',
 ['limit' => 2]);
```
