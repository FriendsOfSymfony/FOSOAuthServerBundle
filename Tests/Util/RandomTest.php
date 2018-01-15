<?php

namespace FOS\OAuthServerBundle\Tests\Util;

use FOS\OAuthServerBundle\Util\Random;
use phpmock\phpunit\PHPMock;

/**
 * Class RandomTest
 *
 * @author Nikola Petkanski <nikola@petkanski.com
 */
class RandomTest extends \PHPUnit_Framework_TestCase
{
    use PHPMock;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @runInSeparateProcess
     */
    public function testGenerateTokenWillUseRandomBytesIfAvailable()
    {
        $hashResult = \random_bytes(32);

        $this->getFunctionMock('FOS\OAuthServerBundle\Util', 'random_bytes')
            ->expects($this->once())
            ->with(32)
            ->willReturn($hashResult)
        ;

        $bin2hexResult = \bin2hex($hashResult);
        $this->getFunctionMock('FOS\OAuthServerBundle\Util', 'bin2hex')
            ->expects($this->once())
            ->with($hashResult)
            ->willReturn($bin2hexResult)
        ;

        $baseConvertResult = \base_convert($bin2hexResult, 16, 36);
        $this->getFunctionMock('FOS\OAuthServerBundle\Util', 'base_convert')
            ->expects($this->once())
            ->with($bin2hexResult, 16, 36)
            ->willReturn($baseConvertResult)
        ;

        $this->assertSame($baseConvertResult, Random::generateToken());
    }
}