A Note About Security
=====================

## OAuth Implementation with Flawed Session Management

As described in this great article about [OAuth Authorization attacks](http://software-security.sans.org/blog/2011/03/07/oauth-authorization-attacks-secure-implementation),
if you use the same firewall for the `authorization` page, and for the rest of your application, a malicious user with access to the unattended
browser can use the user's session.

To protect against that, the FOSOAuthServerBundle comes with a built-in solution.
In the `loginAction()` of your `SecurityController`, just add few lines before to render the response:

``` php
<?php
// src/Acme/SecurityBundle/Controller/SecurityController.php

namespace Acme\SecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        // Add the following lines
        if ($session->has('_security.target_path')) {
            if (false !== strpos($session->get('_security.target_path'), $this->generateUrl('fos_oauth_server_authorize'))) {
                $session->set('_fos_oauth_server.ensure_logout', true);
            }
        }

        return $this->render('AcmeSecurityBundle:Security:login.html.twig', array(
            // last username entered by the user
            'last_username' => $session->get(Security::LAST_USERNAME),
            'error'         => $error,
        ));
    }
}
```

Now, when a user will login in order to access the Authorization page, he will be logged out just after his action.
But, in the same time, if he is already logged, he won't be logged out.


## SSL

Even if the FOSOAuthServerBundle doesn't enforce that, the use of TLS/SSL is the recommended approach.


[Back to index](index.md)
