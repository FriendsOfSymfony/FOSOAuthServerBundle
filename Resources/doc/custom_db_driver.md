Custom db driver.
=================

The bundle provides drivers for Doctrine ORM and Doctrine MongoDB.
Though sometimes you might want to use the bundle with a custom or in-house written storage.
For that, the bundle has support for custom storage. 
Once set, setting manager options in fos_oauth_server.service section becomes mandatory.

Here's an example of custom configuration:

```yaml
# config/packages/fos_oauth_server.yaml

fos_oauth_server:
  db_driver: custom
  service:
    user_provider: 'user_provider_manager_service_id'
    client_manager: 'client_provider_manager_service_id'
    access_token_manager: 'access_token_manager_service_id'
    refresh_token_manager: 'refresh_token_manager_service_id'
    auth_code_manager: 'auth_code_manager_service_id'

```

[Back to index](index.md)

