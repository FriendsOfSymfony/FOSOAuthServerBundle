<?php

declare(strict_types=1);

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Controller;

use FOS\OAuthServerBundle\Model\AccessTokenInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenInterface;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class IntrospectionController
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var TokenManagerInterface
     */
    private $accessTokenManager;

    /**
     * @var TokenManagerInterface
     */
    private $refreshTokenManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TokenManagerInterface $accessTokenManager,
        TokenManagerInterface $refreshTokenManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->accessTokenManager = $accessTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
    }

    public function introspectAction(Request $request): JsonResponse
    {
        // $clientToken = $this->tokenStorage->getToken(); → use in security

        // TODO security for this endpoint. Probably in the README documentation

        $token = $this->getToken($request);

        $isActive = $token && !$token->hasExpired();

        if (!$isActive) {
            return new JsonResponse([
                'active' => false,
            ]);
        }

        return new JsonResponse([
            'active' => true,
            'scope' => $token->getScope(),
            'client_id' => $token->getClientId(),
            'username' => $this->getUsername($token),
            'token_type' => $this->getTokenType($token),
            'exp' => $token->getExpiresAt(),
        ]);
    }

    /**
     * @return TokenInterface|null
     */
    private function getToken(Request $request)
    {
        $tokenTypeHint = $request->request->get('token_type_hint'); // TODO move in a form type ? can be `access_token`, `refresh_token` See https://tools.ietf.org/html/rfc7009#section-4.1.2
        $tokenString = $request->request->get('token'); // TODO move in a form type ?

        $tokenManagerList = [];
        if (!$tokenTypeHint || 'access_token' === $tokenTypeHint) {
            $tokenManagerList[] = $this->accessTokenManager;
        }
        if (!$tokenTypeHint || 'refresh_token' === $tokenTypeHint) {
            $tokenManagerList[] = $this->refreshTokenManager;
        }

        foreach ($tokenManagerList as $tokenManager) {
            $token = $tokenManager->findTokenByToken($tokenString);

            if ($token) {
                return $token;
            }
        }
    }

    /**
     * @return string|null
     */
    private function getTokenType(TokenInterface $token)
    {
        if ($token instanceof AccessTokenInterface) {
            return 'access_token';
        } elseif ($token instanceof RefreshTokenInterface) {
            return 'refresh_token';
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getUsername(TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user) {
            return null;
        }

        return $user->getUserName();
    }
}
