FOSOAuthServerBundle
====================

[![Build Status](https://secure.travis-ci.org/FriendsOfSymfony/FOSOAuthServerBundle.png)](http://travis-ci.org/FriendsOfSymfony/FOSOAuthServerBundle)

## Installation

Installation is a quick 6 step process:

1. Download FOSOAuthServerBundle
2. Configure the Autoloader
3. Enable the Bundle
4. Create your model class
5. Configure your application's security.yml
6. Configure the FOSOAuthServerBundle


### Step 1: Download FOSOAuthServerBundle and oauth2-php

Ultimately, the FOSOAuthServerBundle files should be downloaded to the
`vendor/bundles/FOS/OAuthServerBundle` directory and the `oauth2-php` files to
the `vendor/oauth2-php` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

```
[FOSOAuthServerBundle]
    git=git://github.com/FriendsOfSymfony/FOSOAuthServerBundle.git
    target=bundles/FOS/OAuthServerBundle
    version=1.1.0
[oauth2-php]
    git=git://github.com/FriendsOfSymfony/oauth2-php.git
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, then run the following:

``` bash
$ git submodule add git://github.com/FriendsOfSymfony/FOSOAuthServerBundle.git vendor/bundles/FOS/OAuthServerBundle
$ git submodule add git://github.com/FriendsOfSymfony/oauth2-php.git vendor/oauth2-php
$ git submodule update --init
```


### Step 2: Configure the Autoloader

Add the `FOS` and `OAuth2` namespaces to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'FOS'    => __DIR__.'/../vendor/bundles',
    'OAuth2' => __DIR__.'/../vendor/oauth2-php/lib',
));
```


### Step 3: Enable the bundle

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


### Step 4: Create model classes

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

**a) Doctrine ORM classes**

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

**b) Propel classes**

A `schema.xml` is provided with this bundle to generate Propel classes.
You have to install the [TypehintableBehavior](https://github.com/willdurand/TypehintableBehavior) before to build your model.

By using Git submodules:

    $ git submodule add http://github.com/willdurand/TypehintableBehavior.git vendor/propel-behaviors/TypehintableBehavior

By using the Symfony2 vendor management:

```ini
[TypehintableBehavior]
    git=http://github.com/willdurand/TypehintableBehavior.git
    target=/propel-behaviors/TypehintableBehavior
```

Then, register it:

```ini
# app/config/propel.ini
propel.behavior.typehintable.class = vendor.propel-behaviors.TypehintableBehavior.src.TypehintableBehavior
```

You now can run the following command to create the model:

    $ php app/console propel:model:build

> To create SQL, run the command propel:sql:build and insert it or use migration commands if you have an existing schema in your database.


### Step 5: Configure your application's security.yml

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


### Step 6: Configure FOSOAuthServerBundle

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

The `token` endpoint is at `/oauth/v2/token` by default (see Resources/config/routing/token.xml).

The `authorize` endpoint is at `/oauth/v2/auth` by default (see Resources/config/routing/authorize.xml).


## TODO

- More tests


## Credits

- Arnaud Le Blanc, and [all contributors](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/contributors)
- Inspirated by [BazingaOAuthBundle](https://github.com/willdurand/BazingaOAuthServerBundle) and [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle)
- Installation doc adapted from [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) doc.
