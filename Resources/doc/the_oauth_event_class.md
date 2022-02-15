OAuth Events
====================

When a user accepts to share their data with a client, it's a nice idea to save this state.
By default, the FOSOAuthServerBundle will always show the authorization page to the user
when an access token is asked. As an access token has a lifetime, it can be annoying for your
users to always accept a client.

Thanks to the [Event Dispatcher](http://symfony.com/doc/current/components/event_dispatcher.html),
you can listen before, and after the authorization form process. So, you can save the user's choice,
and even bypass the authorization process. Let's look at an example.

Assuming we have a _Many to Many_ relation between clients, and users. A `PreAuthorizationEvent` or `PostAuthorizationEvent` contains
a `ClientInterface` instance, a `UserInterface` instance (coming from the [Security Component](http://symfony.com/doc/current/book/security.html)),
and a flag to determine whether the client has been accepted, or not.

The following class shows an implementation of a basic listener:

``` php
<?php

namespace Acme\DemoBundle\EventListener;

use FOS\OAuthServerBundle\Event\AbstractAuthorizationEvent;
use FOS\OAuthServerBundle\Event\PostAuthorizationEvent;
use FOS\OAuthServerBundle\Event\PreAuthorizationEvent;

class OAuthEventListener
{
    public function onPreAuthorization(PreAuthorizationEvent $event)
    {
        if ($user = $this->getUser($event)) {
            $event->setAuthorizedClient(
                $user->isAuthorizedClient($event->getClient())
            );
        }
    }

    public function onPostAuthorization(PostAuthorizationEvent $event)
    {
        if ($event->isAuthorizedClient()) {
            $user = $this->getUser($event);
            $user->addClient($event->getClient());
            $user->save();
        }
    }

    protected function getUser(AbstractAuthorizationEvent $event)
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
            - { name: kernel.event_listener, event: FOS\OAuthServerBundle\Event\PreAuthorizationEvent, method: onPreAuthorization }
            - { name: kernel.event_listener, event: FOS\OAuthServerBundle\Event\PostAuthorizationEvent, method: onPostAuthorization }
```


## Using a Symfony EventSubscriber

The name of the event for Symfony's purposes is just the class name of the event class.

```php
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OAuthEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            PreAuthorizationEvent::class  => 'onPreAuthorization',
            PostAuthorizationEvent::class => 'onPostAuthorization',
        ];
    }

    public function onPreAuthorization(PreAuthorizationEvent $event)
    {
    }

    public function onPostAuthorization(PostAuthorizationEvent $event)
    {
    }
}
```

## Next?

You can build a panel for your users displaying this list. If they remove an entry from this list,
then the authorization page will be displayed to the user like the first time. And, if the user
accepts the client, then the system will save this client to the user's list once again.


[Back to index](index.md)
