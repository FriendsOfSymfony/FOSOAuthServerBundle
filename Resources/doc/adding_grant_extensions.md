Adding Grant Extensions
=======================

OAuth2 allows to use [grant extensions](http://tools.ietf.org/html/draft-ietf-oauth-v2-22#section-4.5).
It will let you define your own new *grant_types*, when legacy grant_types do not cover your requirements.

Like classic grants, the grant extensions still requires valid client credentials, and the grant extension has to be added on the `allowedGrantTypes` property of the client.

## Creating the GrantExtension

The GrantExtension class responsibility is to grant an access token, or not, based on the request parameters/headers.

To illustrate the use of a new grant extension, we'll create one that grants access_token like the bingo game. This is of course not something to do :)

### Create your extension class:

``` php
<?php

// src/Acme/ApiBundle/OAuth/BingoGrantExtension.php

namespace Acme\ApiBundle\OAuth;

use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use OAuth2\Model\IOAuth2Client;

/**
 * Play at bingo to get an access_token: May the luck be with you!
 */
class BingoGrantExtension implements GrantExtensionInterface
{
    /*
     * {@inheritdoc}
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        // Check that the input data is correct
        if (!isset($inputData['number_1']) || !isset($inputData['number_2'])) {
            return false;
        }

        $numberToGuess1 = rand(0, 100);
        $numberToGuess2 = rand(0, 100);

        if ($numberToGuess1 != $inputData['number_1'] && $numberToGuess2 != $inputData['number_2']) {
            return false; // No number guessed, grant will fail
        }

        if ($numberToGuess1 == $inputData['number_1'] && $numberToGuess2 == $inputData['number_2']) {
            // Both numbers were guessed, we grant an access_token linked
            // to a user
            return array(
                'data' => $userManager->findRandomUser()
            );
        }

        if ($numberToGuess1 == $inputData['number_1'] || $numberToGuess2 == $inputData['number_2']) {
            // Only one of the numbers were guessed
            // We grant a simple access token

            return true;
        }

        return false; // No number guessed, the grant will fail
    }
}
```

### Register the extension

You then have to register your class as a service, add the tag `fos_oauth_server.grant_extension` with the uri parameter (ie: the name of your grant type).

``` yaml
services:
    acme.api.oauth.bingo_extension:
        class: Acme\ApiBundle\OAuth\BingoGrantExtension
        tags:
            - { name: fos_oauth_server.grant_extension, uri: 'http://acme.com/grants/bingo' }
```

### Usage

```
$ curl -XGET "http://acme.com/oauth/v2/token? \
grant_type=http://acme.com/grants/bingo& \
client_id=1_1& \
client_secret=secret& \
number_1=42& \
number_2=66"
```
