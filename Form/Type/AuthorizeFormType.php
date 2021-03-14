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

use FOS\OAuthServerBundle\Form\Model\Authorize;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $hiddenType = HiddenType::class;

        $builder->add('client_id', $hiddenType);
        $builder->add('response_type', $hiddenType);
        $builder->add('redirect_uri', $hiddenType);
        $builder->add('state', $hiddenType);
        $builder->add('scope', $hiddenType);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Authorize::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'fos_oauth_server_authorize';
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }
}
