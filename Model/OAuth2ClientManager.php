<?php

namespace Alb\OAuth2ServerBundle\Model;

abstract class OAuth2ClientManager implements OAuth2ClientManagerInterface
{
    public function createClient()
    {
        $class = $this->getClass();
        return new $class;
    }

    public function findClientByPublicId($publicId)
    {
        if (false === $pos = strpos($publicId, '_')) {
            return null;
        }

        $id = substr($publicId, 0, $pos);
        $randomId = substr($publicId, $pos+1);

        return $this->findClientBy(array(
            'id' => $id,
            'randomId' => $randomId,
        ));
    }
}

