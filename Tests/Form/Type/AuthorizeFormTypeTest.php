<?php

/*
 * This file is part of the FOSOAuthServerBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\OAuthServerBundle\Tests\Form\Type;

use FOS\OAuthServerBundle\Form\Type\AuthorizeFormType;
use FOS\OAuthServerBundle\Form\Model\Authorize;
use FOS\OAuthServerBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;

class AuthorizeFormTypeTest extends TypeTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
        ->addTypes($this->getTypes())
        ->getFormFactory();

        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    public function testSubmit()
    {
        $accepted = 'true';
        $formData = array(
            'client_id'     => '1',
            'response_type' => 'code',
            'redirect_uri'  => 'http:\\localhost\test.php',
            'state'         => 'testState',
            'scope'         => 'testScope',
        );

        $authorize = new Authorize($accepted, $formData);

        $form = $this->factory->create(LegacyFormHelper::getType('FOS\OAuthServerBundle\Form\Type\AuthorizeFormType'), $authorize);

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($authorize, $form->getData());
        $this->assertEquals((bool) $accepted, $authorize->accepted);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }

    protected function getTypes()
    {
        return  array(
            new AuthorizeFormType(),
        );
    }
}
