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

namespace FOS\OAuthServerBundle\Form\Type;

use FOS\OAuthServerBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Chris Jones <leeked@gmail.com>
 */
class AuthorizeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $hiddenType = LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\HiddenType');

        $builder->add('client_id', $hiddenType);
        $builder->add('response_type', $hiddenType);
        $builder->add('redirect_uri', $hiddenType);
        $builder->add('state', $hiddenType);
        $builder->add('scope', $hiddenType);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'FOS\OAuthServerBundle\Form\Model\Authorize',
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'fos_oauth_server_authorize';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
