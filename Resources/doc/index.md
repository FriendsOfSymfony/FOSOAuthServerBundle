Getting Started With FOSOAuthServerBundle
=========================================

#### Translations

If you wish to use default texts provided in this bundle, you have to make sure you have translator enabled in your config:

    # app/config/config.yml

    framework:
        translator: { fallback: en }

For more information about translations, check [Symfony documentation](http://symfony.com/doc/current/book/translation.html).


## Installation

Installation is a quick 5 steps process:

1. Download FOSOAuthServerBundle
2. Enable the Bundle
3. Create your model class
4. Configure your application's security.yml
5. Configure the FOSOAuthServerBundle


### Step 1: Install FOSOAuthServerBundle

The preferred way to install this bundle is to rely on [Composer](http://getcomposer.org).
Just add it to your `composer.json`:

``` js
{
    "require": {
        // ...
        "friendsofsymfony/oauth-server-bundle": "1.1.0"
    }
}
```


### Step 2: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
    );
}
```


### Step 3: Create model classes

This bundle needs to persist some classes to a database:

- `Client` (OAuth2 consumers)
- `AccessToken`
- `RefreshToken`
- `AuthCode`

Your first job, then, is to create these classes for your application.
These classes can look and act however you want: add any
properties or methods you find useful.

These classes have just a few requirements:

1. They must extend one of the base classes from the bundle
2. They must have an `id` field

In the following sections, you'll see examples of how your classes should
look, depending on how you're storing your data.

Your classes can live inside any bundle in your application. For example,
if you work at "Acme" company, then you might create a bundle called `AcmeApiBundle`
and place your classes in it.

**Warning:**

> If you override the __construct() method in your classs, be sure
> to call parent::__construct(), as the base class depends on
> this to initialize some fields.


#### Doctrine ORM classes

If you're persisting your data via the Doctrine ORM, then your classes
should live in the `Entity` namespace of your bundle and look like this to
start:

``` php
<?php
// src/Acme/ApiBundle/Entity/Client.php

namespace Acme\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

``` php
<?php
// src/Acme/ApiBundle/Entity/AccessToken.php

namespace Acme\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AccessToken extends BaseAccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

}
```

``` php
<?php
// src/Acme/ApiBundle/Entity/RefreshToken.php

namespace Acme\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class RefreshToken extends BaseRefreshToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

}
```

``` php
<?php
// src/Acme/ApiBundle/Entity/AuthCode.php

namespace Acme\ApiBundle\Entity;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

}
```

#### Propel

A `schema.xml` is provided with this bundle to generate Propel classes.
You have to install the [TypehintableBehavior](https://github.com/willdurand/TypehintableBehavior) before to build your model.

By using [Composer](http://getcomposer.org), you just have to add the following line in your `composer.json`:

``` js
{
    "require": {
        "willdurand/propel-typehintable-behavior": "*"
    }
}
```

By using Git submodules:

    $ git submodule add http://github.com/willdurand/TypehintableBehavior.git vendor/willdurand/propel-typehintable-behavior

By using the Symfony2 vendor management:

```ini
[TypehintableBehavior]
    git=http://github.com/willdurand/TypehintableBehavior.git
    target=/willdurand/propel-typehintable-behavior
```

Then, register it:

```ini
# app/config/propel.ini
propel.behavior.typehintable.class = vendor.willdurand.propel-typehintable-behavior.src.TypehintableBehavior
```

You now can run the following command to create the model:

    $ php app/console propel:model:build

> To create SQL, run the command propel:sql:build and insert it or use migration commands if you have an existing schema in your database.


### Step 4: Configure your application's security.yml

In order for Symfony's security component to use the FOSOAuthServerBundle, you must
tell it to do so in the `security.yml` file. The `security.yml` file is where the
basic configuration for the security for your application is contained.

Below is a minimal example of the configuration necessary to use the FOSOAuthServerBundle
in your application:

``` yaml
# app/config/security.yml
security:
    firewalls:
        oauth_token:
            pattern:    ^/oauth/v2/token
            security:   false

        oauth_authorize:
            pattern:    ^/oauth/v2/auth
            # Add your favorite authentication process here

        api:
            pattern:    ^/api
            fos_oauth:  true
            stateless:  true

    access_control:
        # You can omit this if /api can be accessed both authenticated and anonymously
        - { path: ^/api, roles: [ IS_AUTHENTICATED_FULLY ] }
```

The URLs under `/api` will use OAuth2 to authenticate users.


### Step 5: Configure FOSOAuthServerBundle

Import the routing.yml configuration file in app/config/routing.yml:

``` yaml
# app/config/routing.yml
fos_oauth_server_token:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/token.xml"

fos_oauth_server_authorize:
    resource: "@FOSOAuthServerBundle/Resources/config/routing/authorize.xml"
```

Add FOSOAuthServerBundle settings in app/config/config.yml:

``` yaml
# app/config/config.yml
fos_oauth_server:
    db_driver: orm       # Driver availables: orm, mongodb, or propel
    client_class:        Acme\ApiBundle\Entity\Client
    access_token_class:  Acme\ApiBundle\Entity\AccessToken
    refresh_token_class: Acme\ApiBundle\Entity\RefreshToken
    auth_code_class:     Acme\ApiBundle\Entity\AuthCode
```

With Propel for example, you can use the default classes:

``` yaml
# app/config/config.yml
fos_oauth_server:
    db_driver: propel
    client_class:        FOS\OAuthServerBundle\Propel\Client
    access_token_class:  FOS\OAuthServerBundle\Propel\AccessToken
    refresh_token_class: FOS\OAuthServerBundle\Propel\RefreshToken
    auth_code_class:     FOS\OAuthServerBundle\Propel\AuthCode
```

Last step, import the security.yml configuration file in app/config/config.yml:

``` yaml
# app/config/config.yml
imports:
    - { resource: "@FOSOAuthServerBundle/Resources/config/security.yml" }
```

## Usage

The `token` endpoint is at `/oauth/v2/token` by default (see `Resources/config/routing/token.xml`).

The `authorize` endpoint is at `/oauth/v2/auth` by default (see `Resources/config/routing/authorize.xml`).


## Next steps

[A Note About Security](a_note_about_security.md)

[Dealing With Scopes](dealing_with_scopes.md)

[Extending the Authorization page](extending_the_authorization_page.md)

[Extending the Model](extending_the_model.md)

[The OAuthEvent class](the_oauth_event_class.md)
