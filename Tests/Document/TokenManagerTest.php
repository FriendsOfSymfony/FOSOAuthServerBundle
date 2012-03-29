<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Document;

class TokenManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $tokenManager;

    public function setUp()
    {
        if (!class_exists('\Doctrine\ODM\MongoDB\DocumentManager')) {
            $this->markTestSkipped('Doctrine MongoDB ODM has to be installed for this test to run.');
        }

        $this->tokenManager = $this->getManagerMock();
    }

    protected function tearDown()
    {
        unset($this->tokenManager);
    }

    protected function getManagerMock()
    {
        return $this->getMockBuilder('FOS\OAuthServerBundle\Document\TokenManager')
            ->disableOriginalConstructor()
            ->setMethods(array('findTokenBy', 'updateToken', 'deleteToken', 'deleteExpired'))
            ->getMock();
    }
}