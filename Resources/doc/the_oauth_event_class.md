The OAuthEvent class
====================

When a user accepts to share his data with a client, it's a nice idea to save this state.
By default, the FOSOAuthServerBundle will always show the authorization page to the user
when an access token is asked. As an access token has a lifetime, it can be annoying for your
users to always accept a client.

Thanks to the [Event Dispatcher](http://symfony.com/doc/current/components/event_dispatcher.html),
you can listen before, and after the authorization form process. So, you can save the user's choice,
and even bypass the authorization process. Let's look at an example.

Assuming we have a _Many to Many_ relation between clients, and users. An `OAuthEvent` contains
a `ClientInterface` instance, a `UserInterface` instance (coming from the [Security Component](http://symfony.com/doc/current/book/security.html)),
and a flag to determine whether the client has been accepted, or not.

### Registering the listener

``` yaml
services:
    oauth_event_listener:
        class:  Acme\DemoBundle\EventListener\OAuthEventListener
        tags:
            - { name: kernel.event_listener, event: fos_oauth_server.pre_authorization_process, method: onPreAuthorizationProcess }
            - { name: kernel.event_listener, event: fos_oauth_server.post_authorization_process, method: onPostAuthorizationProcess }
```


### Next?

You can build a panel for your users displaying this list. If they remove an entry from this list,
then the authorization page will be displayed to the user like the first time. And, if the user
accepts the client, then the system will save this client to the user's list once again.


[Back to index](index.md)
