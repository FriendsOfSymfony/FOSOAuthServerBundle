<?php

namespace FOS\OAuthServerBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use FOS\OAuthServerBundle\Form\Model\Authorize;

class AuthorizeFormHandler
{
    protected $request;
    protected $form;

    public function __construct(Form $form, Request $request)
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
            $this->form->bindRequest($this->request);
            if ($this->form->isValid()) {
                $this->onSuccess();
                return true;
            }
        }

        return false;
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
