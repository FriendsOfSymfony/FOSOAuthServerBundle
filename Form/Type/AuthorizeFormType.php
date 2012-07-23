<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('client_id', 'hidden');
        $builder->add('response_type', 'hidden');
        $builder->add('redirect_uri', 'hidden');
        $builder->add('state', 'hidden');
        $builder->add('scope', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FOS\OAuthServerBundle\Form\Model\Authorize'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'fos_oauth_server_authorize';
    }
}
