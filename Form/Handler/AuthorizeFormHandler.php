<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\OAuthServerBundle\Form\Model\Authorize;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeFormHandler
{
    protected $request;
    protected $form;

    public function __construct(FormInterface $form, Request $request)
    {
        $this->form = $form;
        $this->request = $request;
    }

    public function isAccepted()
    {
        return $this->form->getData()->accepted;
    }

    public function isRejected()
    {
        return !$this->form->getData()->accepted;
    }

    public function process()
    {
        $this->form->setData(new Authorize(
            $this->request->request->has('accepted'),
            $this->request->query->all()
        ));

        if ('POST' === $this->request->getMethod()) {
            $this->form->bind($this->request);
            if ($this->form->isValid()) {
                $this->onSuccess();

                return true;
            }
        }

        return false;
    }

    public function getScope()
    {
        return $this->form->getData()->scope;
    }

    /**
     * Put form data in $_GET so that OAuth2 library will call Request::createFromGlobals()
     *
     * @todo finishClientAuthorization() is a bit odd since it accepts $data
     *       but then proceeds to ignore it and forces everything to be in $request->query
     */
    protected function onSuccess()
    {
        $_GET = array(
            'client_id'     => $this->form->getData()->client_id,
            'response_type' => $this->form->getData()->response_type,
            'redirect_uri'  => $this->form->getData()->redirect_uri,
            'state'         => $this->form->getData()->state,
            'scope'         => $this->form->getData()->scope,
        );
    }
}
