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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\OAuthServerBundle\Util\LegacyFormHelper;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('client_id', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType'));
        $builder->add('response_type', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType'));
        $builder->add('redirect_uri', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType'));
        $builder->add('state', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType'));
        $builder->add('scope', LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType'));
    }

    /**
     * {@inheritdoc}
     *
     * @todo Remove it when bumping requirements to SF 2.7+
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FOS\OAuthServerBundle\Form\Model\Authorize',
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
