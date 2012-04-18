Dealing With Scopes
===================

OAuth2 allows to use [access token scopes](http://tools.ietf.org/html/draft-ietf-oauth-v2-22#section-3.3).
Scopes are what you want, there is not real constraint except to list scopes as a list of strings separated by a space:

    scope1 scope2

That's why the `scope` column in the model layer is a string, not an array for instance.


## Configuring scopes

To configure allowed scopes in your application, you have to edit your `app/config/config.yml` file:

``` yaml
# app/config/config.yml
fos_oauth_server:
    service:
        options:
            supported_scopes: scope1 scope2
```

Now, clients will be able to pass a `scope` parameter when they request an access token.


## Using scopes

The default behavior of the FOSOAuthServerBundle is to use scopes as [roles](http://symfony.com/doc/master/book/security.html#roles).
In the previous example, it would allow us to use the roles `ROLE_SCOPE1`, and `ROLE_SCOPE2` (scopes are automatically uppercased).

That way, you can configure the `access_control` section of the security layer:

``` yaml
# app/config/security.yml
security:
    access_control:
        - { path: ^/api/super/secured, role: ROLE_SCOPE1 }
```

For more information, you can read the [Security documentation](http://symfony.com/doc/master/book/security.html#authorization).


[Back to index](index.md)
