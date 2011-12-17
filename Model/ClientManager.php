<?php

namespace FOS\OAuthServerBundle\Model;

abstract class ClientManager implements ClientManagerInterface
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

