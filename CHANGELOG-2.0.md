CHANGELOG for 2.0.x
===================

This changelog references the relevant changes done in 6.0 versions.

### 2.0.0-ALPHA1 (unreleased)

* Added `NL` translations [[#631](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/631)]
* Bumped `twig/twig` supported versions to `1.40` for `1.x` and `2.9` for `2.x` [[#652](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/652)]
* Dropped support for PHP 7.1 [[#651](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/651)]
* Dropped support for Symfony versions anterior to `4.4` [[#648](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/648)]
* Fixed form submission/validation [[#643](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/643)]
* **[BC break]** Changed signature of method `FOS\OAuthServerBundle\Controller\AuthorizeController::renderAuthorize()` [[#653](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/653)]
* **[BC break]** Removed support for templating engine [[#653](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/653)]

### 2.0.0-ALPHA0 (2018-05-01)

* Deprecated support for Symfony 2.x
* Dropped support for PHP < 7.1
* Allowed array in `supported_scopes` option [[#552](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/552)]
* Allowed overriding of `authorizeAction` rendering [[#564](https://github.com/FriendsOfSymfony/FOSOAuthServerBundle/pull/564)]
