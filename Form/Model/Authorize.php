<?php

namespace FOS\OAuthServerBundle\Form\Model;

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
     * @param bool $accepted
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
