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

namespace FOS\OAuthServerBundle\Tests\Form\Type;

use FOS\OAuthServerBundle\Form\Model\Authorize;
use FOS\OAuthServerBundle\Form\Type\AuthorizeFormType;
use FOS\OAuthServerBundle\Util\LegacyFormHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorizeFormTypeTest extends TypeTestCase
{
    /**
     * @var AuthorizeFormType
     */
    protected $instance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypes($this->getTypes())
            ->getFormFactory()
        ;

        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);

        $this->instance = new AuthorizeFormType();
    }

    public function testSubmit(): void
    {
        $accepted = true;
        $formData = [
            'client_id' => '1',
            'response_type' => 'code',
            'redirect_uri' => 'http:\\localhost\test.php',
            'state' => 'testState',
            'scope' => 'testScope',
        ];

        $authorize = new Authorize($accepted, $formData);

        $form = $this->factory->create(LegacyFormHelper::getType(AuthorizeFormType::class), $authorize);

        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertSame($authorize, $form->getData());
        self::assertSame($accepted, $authorize->accepted);

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            self::assertArrayHasKey($key, $children);
        }
    }

    public function testConfigureOptionsWillSetDefaultsOnTheOptionsResolver(): void
    {
        /** @var MockObject|OptionsResolver $resolver */
        $resolver = $this->getMockBuilder(OptionsResolver::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $resolver
            ->expects($this->once())
            ->method('setDefaults')
            ->with([
                'data_class' => Authorize::class,
                'validation_groups' => [],
            ])
            ->willReturn($resolver)
        ;

        self::assertNull($this->instance->configureOptions($resolver));
    }

    public function testGetName(): void
    {
        self::assertSame('fos_oauth_server_authorize', $this->instance->getName());
    }

    public function testGetBlockPrefix(): void
    {
        self::assertSame('fos_oauth_server_authorize', $this->instance->getBlockPrefix());
    }

    /**
     * @return array<AbstractType>
     */
    protected function getTypes(): array
    {
        return  [
            new AuthorizeFormType(),
        ];
    }
}
