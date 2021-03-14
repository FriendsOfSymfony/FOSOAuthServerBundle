<?php

namespace FOS\OAuthServerBundle\Tests\DependencyInjection;

use FOS\OAuthServerBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testShouldImplementConfigurationInterface(): void
    {
        $rc = new ReflectionClass(Configuration::class);

        self::assertTrue($rc->implementsInterface(ConfigurationInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments(): void
    {
        new Configuration();
    }

    public function testShouldNotMandatoryServiceIfNotCustomDriverIsUsed(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration(
            $configuration,
            [
                [
                    'db_driver' => 'orm',
                    'client_class' => 'aClientClass',
                    'access_token_class' => 'anAccessTokenClass',
                    'refresh_token_class' => 'aRefreshTokenClass',
                    'auth_code_class' => 'anAuthCodeClass',
                ],
            ]
        );
    }

    public function testShouldMakeClientManagerServiceMandatoryIfCustomDriverIsUsed(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(
            InvalidConfigurationException::class,
            'Invalid configuration for path "fos_oauth_server": The service client_manager must be set explicitly for custom db_driver.'
        );

        $processor->processConfiguration(
            $configuration,
            [
                [
                    'db_driver' => 'custom',
                    'client_class' => 'aClientClass',
                    'access_token_class' => 'anAccessTokenClass',
                    'refresh_token_class' => 'aRefreshTokenClass',
                    'auth_code_class' => 'anAuthCodeClass',

                ],
            ]
        );
    }

    public function testShouldMakeAccessTokenManagerServiceMandatoryIfCustomDriverIsUsed(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(
            InvalidConfigurationException::class,
            'Invalid configuration for path "fos_oauth_server": The service access_token_manager must be set explicitly for custom db_driver.'
        );

        $processor->processConfiguration(
            $configuration,
            [
                [
                    'db_driver' => 'custom',
                    'client_class' => 'aClientClass',
                    'access_token_class' => 'anAccessTokenClass',
                    'refresh_token_class' => 'aRefreshTokenClass',
                    'auth_code_class' => 'anAuthCodeClass',
                    'service' => [
                        'client_manager' => 'a_client_manager_id',
                    ],
                ],
            ]
        );
    }

    public function testShouldMakeRefreshTokenManagerServiceMandatoryIfCustomDriverIsUsed(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(
            InvalidConfigurationException::class,
            'Invalid configuration for path "fos_oauth_server": The service refresh_token_manager must be set explicitly for custom db_driver.'
        );

        $processor->processConfiguration(
            $configuration,
            [
                [
                    'db_driver' => 'custom',
                    'client_class' => 'aClientClass',
                    'access_token_class' => 'anAccessTokenClass',
                    'refresh_token_class' => 'aRefreshTokenClass',
                    'auth_code_class' => 'anAuthCodeClass',
                    'service' => [
                        'client_manager' => 'a_client_manager_id',
                        'access_token_manager' => 'anId',
                    ],
                ],
            ]
        );
    }

    public function testShouldMakeAuthCodeManagerServiceMandatoryIfCustomDriverIsUsed(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(
            InvalidConfigurationException::class,
            'Invalid configuration for path "fos_oauth_server": The service auth_code_manager must be set explicitly for custom db_driver.'
        );

        $processor->processConfiguration(
            $configuration,
            [
                [
                    'db_driver' => 'custom',
                    'client_class' => 'aClientClass',
                    'access_token_class' => 'anAccessTokenClass',
                    'refresh_token_class' => 'aRefreshTokenClass',
                    'auth_code_class' => 'anAuthCodeClass',
                    'service' => [
                        'client_manager' => 'a_client_manager_id',
                        'access_token_manager' => 'anId',
                        'refresh_token_manager' => 'anId',

                    ],
                ],
            ]
        );
    }

    public function testShouldLoadCustomDriverConfig(): void
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration(
            $configuration,
            [
                [
                    'db_driver' => 'custom',
                    'client_class' => 'aClientClass',
                    'access_token_class' => 'anAccessTokenClass',
                    'refresh_token_class' => 'aRefreshTokenClass',
                    'auth_code_class' => 'anAuthCodeClass',
                    'service' => [
                        'client_manager' => 'a_client_manager_id',
                        'access_token_manager' => 'an_access_token_manager_id',
                        'refresh_token_manager' => 'a_refresh_token_manager_id',
                        'auth_code_manager' => 'an_auth_code_manager_id',
                    ],
                ],
            ]
        );
    }
}
