Introspection endpoint
=========================================

The OAuth 2.0 Token Introspection extension defines a protocol that returns information about an access token, intended to be used by resource servers or other internal servers.

For more information, see [this explaination](https://www.oauth.com/oauth2-servers/token-introspection-endpoint/) or [the RFC 7662](https://tools.ietf.org/html/rfc7662).

## Configuration

Import the routing.yml configuration file in `app/config/routing.yml`:

```yaml
# app/config/routing.yml

fos_oauth_server_introspection:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/introspection.xml"
```

Add FOSOAuthServerBundle settings in `app/config/config.yml`:

```yaml
fos_oauth_server:
      introspection:
          allowed_clients:
              - 1_wUS0gjHdHyC2qeBL3u7RuIrIXClt6irL # an oauth client used only for token introspection. 
```

The allowed clients MUST be clients as defined [here](index.md#creating-a-client) and SHOULD be used only for token introspection (otherwise a endpoint client might call the introspection endpoint with its valid token).


The introspection endpoint must be behind a firewall defined like this:

```yaml
# app/config/security.yml
security:
    firewalls:
         oauth_introspect:
              host: "%domain.oauth2%"
              pattern: ^/oauth/v2/introspect
              fos_oauth: true
              stateless: true
              anonymous: false
```

### Usage

Then you can call the introspection endpoint like this:

```
POST /token_info
Host: authorization-server.com
Authorization: Bearer KvIu5v90GqgDctofFXP8npjC5DzMUkci
 
token=SON4N82oVuRFykExk0iGTghihgOcI6bm
```

The JSON response will look like this if the token is inactive:

```json
{
  "active": false
}
```

If the token is active, the response will look like this:

```json
{
  "active": true,
  "scope": "scope1 scope2",
  "client_id": "2_HC1KF0UrawHx05AxgNEeKJF10giBUOHZ",
  "username": "foobar",
  "token_type": "access_token",
  "exp": 1534921182
}
```
