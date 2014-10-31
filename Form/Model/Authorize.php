<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Form\Model;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class Authorize
{
    /**
     * @var bool
     */
    public $accepted;

    /**
     * @var string
     */
    public $client_id;

    /**
     * @var string
     */
    public $response_type;

    /**
     * @var string
     */
    public $redirect_uri;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $scope;

    /**
     * @param bool  $accepted
     * @param array $query
     */
    public function __construct($accepted, array $query = array())
    {
        foreach ($query as $key => $value) {
            $this->{$key} = $value;
        }

        $this->accepted = (bool) $accepted;
    }
}
