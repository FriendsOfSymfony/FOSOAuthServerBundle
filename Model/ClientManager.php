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

namespace FOS\OAuthServerBundle\Model;

abstract class ClientManager implements ClientManagerInterface
{
    /**
     * {@inheritdoc}
     */
    public function createClient()
    {
        $class = $this->getClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function findClientByPublicId($publicId)
    {
        if (false === $pos = mb_strpos($publicId, '_')) {
            return null;
        }

        $id = mb_substr($publicId, 0, $pos);
        $randomId = mb_substr($publicId, $pos + 1);

        return $this->findClientBy([
            'id' => $id,
            'randomId' => $randomId,
        ]);
    }
}
