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

namespace FOS\OAuthServerBundle\Form\Handler;

use FOS\OAuthServerBundle\Form\Model\Authorize;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeFormHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestStack|Request|null
     */
    private $requestStack;

    /**
     * @param Request|RequestStack $requestStack
     */
    public function __construct(FormInterface $form, $requestStack = null)
    {
        if (null !== $requestStack && !$requestStack instanceof RequestStack && !$requestStack instanceof Request) {
            throw new \InvalidArgumentException(sprintf('Argument 2 of %s must be an instanceof RequestStack or Request', __CLASS__));
        }

        $this->form = $form;
        $this->requestStack = $requestStack;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function isAccepted()
    {
        return $this->form->getData()->accepted;
    }

    public function isRejected()
    {
        return !$this->form->getData()->accepted;
    }

    /**
     * @return bool
     */
    public function process()
    {
        $request = $this->getCurrentRequest();

        if (null === $request) {
            return false;
        }

        $this->form->setData(new Authorize(
            $request->request->has('accepted'),
            $request->query->all()
        ));

        if ('POST' !== $request->getMethod()) {
            return false;
        }

        if (!$this->form->handleRequest($request)->isSubmitted() || !$this->form->isValid()) {
            return false;
        }

        $this->onSuccess();

        return true;
    }

    public function getScope()
    {
        return $this->form->getData()->scope;
    }

    /**
     * Put form data in $_GET so that OAuth2 library will call Request::createFromGlobals().
     *
     * @todo finishClientAuthorization() is a bit odd since it accepts $data
     *       but then proceeds to ignore it and forces everything to be in $request->query
     */
    protected function onSuccess()
    {
        $_GET = [
            'client_id' => $this->form->getData()->client_id,
            'response_type' => $this->form->getData()->response_type,
            'redirect_uri' => $this->form->getData()->redirect_uri,
            'state' => $this->form->getData()->state,
            'scope' => $this->form->getData()->scope,
        ];
    }

    private function getCurrentRequest()
    {
        if (null === $this->requestStack) {
            return $this->container->get('request');
        }

        if ($this->requestStack instanceof Request) {
            return $this->requestStack;
        }

        return $this->requestStack->getCurrentRequest();
    }
}
