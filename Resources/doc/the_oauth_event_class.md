The OAuthEvent class
====================

When a user accepts to share his data with a client, it's a nice idea to save this state.
By default, the FOSOAuthServerBundle will always show the authorization page to the user
when an access token is asked. As an access token has a lifetime, it can be annoying for your
users to always accept a client.

Thanks to the [Event Dispatcher](http://symfony.com/doc/current/components/event_dispatcher.html),
you can listen before, and after the authorization form process. So, you can save the user's choice,
and even by pass the authorization pass. Let's an example.

Assuming we have a _Many to Many_ relation between clients, and users. An `OAuthEvent` contains
a `ClientInterface` instance, a `UserInterface` instance (coming from the [Security Component](http://symfony.com/doc/current/book/security.html)),
and a flag to determine whether the client has been accepted, or not.

The following class shows a Propel implementation of a basic listener:

``` php
<?php

namespace Acme\DemoBundle\EventListener;

use FOS\OAuthServerBundle\Event\OAuthEvent;

class OAuthEventListener
{
    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        if ($user = $this->getUser($event)) {
            $event->setAuthorizedClient(
                $user->isAuthorizedClient($event->getClient())
            );
        }
    }

    public function onPostAuthorizationProcess(OAuthEvent $event)
    {
        if ($event->isAuthorizedClient()) {
            if (null !== $client = $event->getClient()) {
                $user = $this->getUser($event);
                $user->addClient($client);
                $user->save();
            }
        }
    }

    protected function getUser(OAuthEvent $event)
    {
        return UserQuery::create()
            ->filterByUsername($event->getUser()->getUsername())
            ->findOne();
    }
}
```

The `$user` variable has a method `isAuthorizedClient()` which contains your logic to determine whether
the given client (`ClientInterface`) is allowed by the user, or not. This `$user` is part of your
own model layer, and loaded using the `username` property (see `getUser()`).

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
