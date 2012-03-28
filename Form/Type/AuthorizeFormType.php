<?php

namespace FOS\OAuthServerBundle\Form\Type;

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\AbstractType;

class AuthorizeFormType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('client_id', 'hidden');
        $builder->add('response_type', 'hidden');
        $builder->add('redirect_uri', 'hidden');
        $builder->add('state', 'hidden');
        $builder->add('scope', 'hidden');
    }

    /**
     * @param array $options
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'FOS\OAuthServerBundle\Form\Model\Authorize'
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fos_oauth_server_authorize';
    }
}
