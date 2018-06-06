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

namespace FOS\OAuthServerBundle\Tests\Storage;

use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use FOS\OAuthServerBundle\Storage\PasswordChecker;

class PasswordCheckerTest extends \PHPUnit\Framework\TestCase
{
    protected $encoderFactory;

    protected $passwordChecker;

    public function setUp()
    {
        $this->encoderFactory = $this->getMockBuilder(EncoderFactoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->passwordChecker = new PasswordChecker($this->encoderFactory);
    }

    public function testValidateReturnsTrueOnValidCredentials()
    {
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $user->expects($this->once())
            ->method('getPassword')->with()->will($this->returnValue('foo'));
        $user->expects($this->once())
            ->method('getSalt')->with()->will($this->returnValue('bar'));

        $encoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->will($this->returnValue(true))
        ;

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with($user)
            ->will($this->returnValue($encoder))
        ;

        $this->assertTrue($this->passwordChecker->validate($user, 'baz'));
    }

    public function testValidateReturnsFalseOnInvalidCredentials()
    {
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $user->expects($this->once())
            ->method('getPassword')->with()->will($this->returnValue('foo'));
        $user->expects($this->once())
            ->method('getSalt')->with()->will($this->returnValue('bar'));

        $encoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $encoder->expects($this->once())
            ->method('isPasswordValid')
            ->with('foo', 'baz', 'bar')
            ->will($this->returnValue(false))
        ;

        $this->encoderFactory->expects($this->once())
            ->method('getEncoder')
            ->with($user)
            ->will($this->returnValue($encoder))
        ;

        $this->assertFalse($this->passwordChecker->validate($user, 'baz'));
    }
}
