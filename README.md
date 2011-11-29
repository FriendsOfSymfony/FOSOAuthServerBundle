AlbOAuth2ServerBundle
=====================

Under development

## Installation

Installation is a quick 6 step process:

1. Download AlbOAuth2ServerBundle
2. Configure the Autoloader
3. Enable the Bundle
4. Create your User class
5. Configure your application's security.yml
6. Configure the AlbOAuth2ServerBundle

### Step 1: Download AlbOAuth2ServerBundle and oauth2-php

Ultimately, the AlbOAuth2ServerBundle files should be downloaded to the
`vendor/bundles/Alb/OAuth2ServerBundle` directory and the oauth2-php files to
the `vendor/oauth2-php` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

```
[AlbOAuth2ServerBundle]
    git=git://github.com/arnaud-lb/AlbOAuth2ServerBundle.git
    target=bundles/Alb/OAuth2ServerBundle
[oauth2-php]
    git=git://github.com/arnaud-lb/oauth2-php.git
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, then run the following:

``` bash
$ git submodule add git://github.com/arnaud-lb/AlbOAuth2ServerBundle.git vendor/bundles/Alb/OAuth2ServerBundle
$ git submodule add git://github.com/arnaud-lb/oauth2-php.git vendor/oauth2-php
$ git submodule update --init
```

### Step 2: Configure the Autoloader

Add the `Alb` and `OAuth2` namespaces to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Alb'    => __DIR__.'/../vendor/bundles',
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
        new Alb\OAuth2ServerBundle\AlbOAuth2ServerBundle(),
    );
}
```

### Step 4: Create model classes

This bundle needs to persist some classes to a database:

- `OAuth2Client` (OAuth2 consumers)
- `OAuth2AccessToken`

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
// src/Acme/ApiBundle/Entity/OAuth2Client.php

namespace Acme\ApiBundle\Entity;

use Alb\OAuth2Server\Entity\OAuth2Client as BaseOAuth2Client;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OAuth2Client extends BaseOAuth2Client
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
// src/Acme/ApiBundle/Entity/OAuth2AccessToken.php

namespace Acme\ApiBundle\Entity;

use Alb\OAuth2Server\Entity\OAuth2AccessToken as BaseOAuth2AccessToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class OAuth2AccessToken extends BaseOAuth2AccessToken
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="OAuth2Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

### Step 5: Configure your application's security.yml

In order for Symfony's security component to use the AlbOAuth2ServerBundle, you must
tell it to do so in the `security.yml` file. The `security.yml` file is where the
basic configuration for the security for your application is contained.

Below is a minimal example of the configuration necessary to use the AlbOAuth2ServerBundle
in your application:

``` yaml
# app/config/security.yml
security:
    firewalls:
        api:
            pattern:    ^/api
            alb_oauth2: true
            stateless:  true

    access_control:
        # You can omit this if /api can be accessed both authenticated and anonymously
        - { path: ^/api, roles: [IS_AUTHENTICATED_FULLY] }
```

The URLs under `/api` will use OAuth2 to authenticate users.

### Step 6: Configure AlbOAuth2ServerBundle

Import the security.yml configuration file in app/config/config.yml:

``` yaml
# app/config/config.yml
imports:
    - { resource: "@AlbOAuth2ServerBundle/Resources/config/security.yml" }
```

Import the routing.yml configuration file in app/config/routing.yml:

``` yaml
# app/config/routing.yml
alb_oauth2:
    resource: "@AlbOAuth2ServerBundle/Resources/config/routing.yml"
```

Add AlbOAuth2ServerBundle settings in app/config/config.yml:


``` yaml
# app/config/config.yml
alb_o_auth2_server:
    db_driver:  orm
    oauth2_client_class:        Acme\ApiBundle\Entity\OAuth2Client
    oauth2_access_token_class:  Acme\ApiBundle\Entity\OAuth2AccessToken
```

## Usage

The `token` endpoint is at `/oauth/v2/token` by default (see Resources/config/routing.yml).

An `authorize` endpoint can be implemented with the `finishClientAuthorization` method on
the `alb.oauth2.server.server_service` service:

``` php
<?php

if ($form->isValid()) {
    try {
        $response = $service->finishClientAuthorization(true, $currentUser, $request, $scope);
        return $response;
    } catch(\OAuth2\OAuth2ServerException $e) {
        return $e->getHttpResponse();
    }
}
```

## TODO

- More tests
- Add model classes for OAuth2AuthCode, OAuth2RefreshToken
- Add methods for authorization_code and refresh_token authorization types in the default storage adapter
- Add a default controler for the /authorize endpoint

## Credits

- Arnaud Le Blanc
- Inspirated by [BazingaOAuthBundle](https://github.com/willdurand/BazingaOAuthServerBundle) and [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle)
- Installation doc adapted from [FOSUserBundle](https://github.com/FriendsOfSymfony/FOSUserBundle) doc.

