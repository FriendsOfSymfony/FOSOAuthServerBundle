FOSOAuthServerBundle Configuration Reference
============================================

All available configuration options are listed below with their default values.

``` yaml
fos_oauth_server:
    db_driver:                  ~ # Required. Available: mongodb, orm, propel
    client_class:               ~ # Required
    access_token_class:         ~ # Required
    refresh_token_class:        ~ # Required
    auth_code_class:            ~ # Required
    model_manager_name:         ~ # change it to the name of your entity/document manager if you don't want to use the default one.
    authorize:
        form:
            type:               fos_oauth_server_authorize
            handler:            fos_oauth_server.authorize.form.handler.default
            name:               fos_oauth_server_authorize_form
            validation_groups:

                # Defaults:
                - Authorize
                - Default
    service:
        storage:                fos_oauth_server.storage.default
        user_provider:          ~
        client_manager:         fos_oauth_server.client_manager.default
        access_token_manager:   fos_oauth_server.access_token_manager.default
        refresh_token_manager:  fos_oauth_server.refresh_token_manager.default
        auth_code_manager:      fos_oauth_server.auth_code_manager.default
        options:
            # Prototype
            key:                []

            # Example
            # supported_scopes: string

            # Changing tokens and authcode lifetime
            #access_token_lifetime: 3600
            #refresh_token_lifetime: 1209600
            #auth_code_lifetime: 30

            # Token type to respond with. Currently only "Bearer" supported.
            #token_type: string

            #realm:

            # Enforce redirect_uri on input for both authorize and token steps.
            #enforce_redirect: true or false

            # Enforce state to be passed in authorization (see RFC 6749, section 10.12)
            #enforce_state: true or false
```

[Back to index](index.md)
