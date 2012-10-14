<?php

namespace FOS\OAuthServerBundle\Tests\Functional;

class BootTest extends TestCase
{
    /**
     * @dataProvider getTestBootData
     */
    public function testBoot($env)
    {
        $kernel = $this->createKernel(array('env' => $env));
        $kernel->boot();
    }

    public function getTestBootData()
    {
        return array(
            array('orm'),
        );
    }
}
