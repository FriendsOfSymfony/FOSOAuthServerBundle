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

namespace FOS\OAuthServerBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Introspect
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $token;

    /**
     * @var string
     */
    public $token_type_hint;
}
