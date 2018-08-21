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

use FOS\OAuthServerBundle\Form\Model\Introspect;
use FOS\OAuthServerBundle\Form\Type\IntrospectionFormType;
use FOS\OAuthServerBundle\Model\AccessTokenInterface;
use FOS\OAuthServerBundle\Model\RefreshTokenInterface;
use FOS\OAuthServerBundle\Model\TokenInterface;
use FOS\OAuthServerBundle\Model\TokenManagerInterface;
use FOS\OAuthServerBundle\Security\Authentication\Token\OAuthToken;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var array
     */
    private $allowedIntrospectionClients;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TokenManagerInterface $accessTokenManager,
        TokenManagerInterface $refreshTokenManager,
        FormFactory $formFactory,
        array $allowedIntrospectionClients
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->accessTokenManager = $accessTokenManager;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->formFactory = $formFactory;
        $this->allowedIntrospectionClients = $allowedIntrospectionClients;
    }

    public function introspectAction(Request $request): JsonResponse
    {
        $clientToken = $this->tokenStorage->getToken(); // → use in security

        if (!$clientToken instanceof OAuthToken) {
            throw new AccessDeniedException('The introspect endpoint must be behind a secure firewall.');
        }

        $callerToken = $this->accessTokenManager->findTokenByToken($clientToken->getToken());

        if (!$callerToken) {
            throw new AccessDeniedException('The access token must have a valid token.');
        }

        if (!in_array($callerToken->getClientId(), $this->allowedIntrospectionClients)) {
            throw new AccessDeniedException('This access token is not autorised to do introspection.');
        }

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
        $formData = $this->processIntrospectionForm($request);
        $tokenString = $formData->token;
        $tokenTypeHint = $formData->token_type_hint;

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

    private function processIntrospectionForm(Request $request): Introspect
    {
        $formData = new Introspect();
        $form = $this->formFactory->create(IntrospectionFormType::class, $formData);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = $form->getErrors();
            if (count($errors) > 0) {
                throw new BadRequestHttpException((string) $errors);
            } else {
                throw new BadRequestHttpException('Introspection endpoint needs to have at least a "token" form parameter');
            }
        }
        return $form->getData();
    }
}
