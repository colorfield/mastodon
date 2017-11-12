## Configuration

Configure OAuth at /admin/config/services/mastodon

1. The configuration form will provide, by default:
a client_id, client_secret and authorization URL.
2. Go to the authorization URL, confirm access, copy 
the authorization code and save configuration.
3. The form will autocomplete the bearer from the access token,
re-save configuration then.

A test of the API will be displayed, getting followers from 
the user 1 of your instance.

## Using the API

The Mastodon class is available as a Drupal service that uses OAuth
credentials defined from the configuration, in the constructor.

Get it via dependency injection ('mastodon.api') then it is ready to use.

```$user = $mastodon->authenticateUser($email, $password);```

Or use the static service container when dependency injection is not available.
```
$mastodon = \Drupal::service('mastodon.api');
$user = $mastodon->authenticateUser($email, $password);
```

### Option 1 - use the Mastodon API

You can still use the API endpoints defined via the
[Mastodon API documentation](goo.gl/bQtGNn).

Currently the scopes are defined as read and write (without follow).

```
$user_id = 1;
$followers = $mastodon->getApi()->get('/accounts/'.$user_id.'/followers',
 ['limit' => 2]);
```

### Option 2 - rely on the Mastodon wrapper

Currently methods are being implemented as a syntactic sugar
[#2920985](https://www.drupal.org/node/2920985).

```
$user_id = 1;
$followers = $mastodon->getFollowers($user_id, ['limit' => 2]);
```

#### Methods to be implemented

Project status for the Mastodon wrapper, by release.

##### Accounts

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Fetching an account                  | GET /api/v1/accounts/:id                       | ✕          | 1.0     | ✓      |
| Getting the current user             | GET /api/v1/accounts/verify_credentials        | ✓          | 1.0     | ✓      |
| Updating the current user            | PATCH /api/v1/accounts/update_credentials      |            |         |        |
| Getting an account's followers       | GET /api/v1/accounts/:id/followers             | ✕          | 1.0     | ✓      |
| Getting who account is following     | GET /api/v1/accounts/:id/following             | ✕          | 1.0     | ✓      |
| Getting an account's statuses        | GET /api/v1/accounts/:id/statuses              | ✕          | 1.0     | ✓      |
| Following/unfollowing an account     | POST /api/v1/accounts/:id/follow - unfollow    |            |         |        |
| Blocking/unblocking an account       | POST /api/v1/accounts/:id/block - unblock      |            |         |        |
| Muting/unmuting an account           | POST /api/v1/accounts/:id/mute - unmute        |            |         |        |
| Getting an account's relationships   | GET /api/v1/accounts/relationships             | ✓          | 1.0     | ✓      |
| Searching for accounts               | GET /api/v1/accounts/search                    | ✕          | 1.0     | ✓      |

##### Apps

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Registering an application           | POST /api/v1/apps                              |            | 1.0     | ✓      |


##### Blocks

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Fetching a user's blocks             | GET /api/v1/blocks                             |            |         |        |

##### Domain blocks

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Fetching a user's blocked domain     | GET /api/v1/domain_block                       |            |         |        |
| Blocking a domain                    | POST /api/v1/domain_block                      |            |         |        |
| Unblocking a domain                  | DELETE /api/v1/domain_block                    |            |         |        |

##### Favourites

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Fetching a user's favourites         | GET /api/v1/favourite                          |            |         |        |

##### Follow Requests

| Description                              | Endpoint                                              | Needs auth | Release | Status |
| ---------------------------------------- | ----------------------------------------------------- |:----------:|:-------:|:------:|
| Fetching a list of follow requests       | GET /api/v1/follow_requests                           |            |         |        |
| Authorizing or rejecting follow requests | POST /api/v1/follow_requests/:id/authorize - reject   |            |         |        |

##### Follow

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Following a remote user              | POST /api/v1/follows                           |            |         |        |

##### Instances

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Getting instance information         | GET /api/v1/instance                           |            |         |        |

##### Medias

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Uploading a media attachment         | POST /api/v1/media                             |            |         |        |

##### Mutes

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Fetching a user's mutes              | GET /api/v1/mutes                              |            |         |        |

##### Notifications

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Fetching a user's notification       | GET /api/v1/notifications                      |            |         |        |
| Getting a single notificatio         | GET /api/v1/notifications/:id                  |            |         |        |
| Clearing notifications               | POST /api/v1/notifications/clear               |            |         |        |
| Dismissing a single notification     | POST /api/v1/notifications/dismiss             |            |         |        |

##### Reports

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Fetching a user's reports            | GET /api/v1/reports                            |            |         |        |
| Reporting a user                     | POST /api/v1/reports                           |            |         |        |

##### Search

| Description                          | Endpoint                                       | Needs auth | Release | Status |
| ------------------------------------ | ---------------------------------------------- |:----------:|:-------:|:------:|
| Searching for content                | GET /api/v1/search                             |            | 1.0     | ✓      |

##### Statuses

| Description                                | Endpoint                                               | Needs auth | Release | Status |
| ------------------------------------------ | ------------------------------------------------------ |:----------:|:-------:|:------:|
| Fetching a status                          | GET /api/v1/statuses/:id                               |            |         |        |
| Getting status context                     | GET /api/v1/statuses/:id/context                       |            |         |        |
| Getting a card associated with a status    | GET /api/v1/statuses/:id/card                          |            |         |        |
| Getting who reblogged/favourited a status  | GET /api/v1/statuses/:id/reblogged_by - favourited_by  |            |         |        |
| Posting a new status                       | POST /api/v1/statuses                                  |            |         |        |
| Deleting a status                          | DELETE /api/v1/statuses/:id                            |            |         |        |
| Reblogging/unreblogging a status           | POST /api/v1/statuses/:id/reblog - unreblog            |            |         |        |
| Favouriting/unfavouriting a status         | POST /api/v1/statuses/:id/favourite - unfavourite      |            |         |        |
| Muting/unmuting a conversation of a status | POST /api/v1/statuses/:id/mute - unmute                |            |         |        |

##### Timelines

| Description                          | Endpoint                                           | Needs auth | Release | Status |
| ------------------------------------ | -------------------------------------------------- |:----------:|:-------:|:------:|
| Retrieving a timeline                | GET /api/v1/timelines/home                         | ✓          | 1.0     | ✓      |
| Retrieving a timeline                | GET /api/v1/timelines/public                       | ✕          | 1.0     | ✓      |
| Retrieving a timeline                | GET /api/v1/timelines/tag/:hashtag                 | ✕          | 1.0     | ✓      |

@todo streaming api https://github.com/tootsuite/documentation/blob/master/Using-the-API/Streaming-API.md
