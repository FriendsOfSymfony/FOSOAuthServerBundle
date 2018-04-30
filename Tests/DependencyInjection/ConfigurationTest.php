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

namespace FOS\OAuthServerBundle\Tests\DependencyInjection;

use FOS\OAuthServerBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldImplementConfigurationInterface()
    {
        $rc = new \ReflectionClass(Configuration::class);

        $this->assertTrue($rc->implementsInterface(ConfigurationInterface::class));
    }

    public function testCouldBeConstructedWithoutAnyArguments()
    {
        try {
            new Configuration();

            // no exceptions were thrown
            self::assertTrue(true);
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testShouldNotMandatoryServiceIfNotCustomDriverIsUsed()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, [[
            'db_driver' => 'orm',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
        ]]);

        $this->assertArraySubset([
            'db_driver' => 'orm',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
            'service' => [
                'storage' => 'fos_oauth_server.storage.default',
                'user_provider' => null,
                'client_manager' => 'fos_oauth_server.client_manager.default',
                'access_token_manager' => 'fos_oauth_server.access_token_manager.default',
                'refresh_token_manager' => 'fos_oauth_server.refresh_token_manager.default',
                'auth_code_manager' => 'fos_oauth_server.auth_code_manager.default',
            ],
        ], $config);
    }

    public function testShouldMakeClientManagerServiceMandatoryIfCustomDriverIsUsed()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "fos_oauth_server": The service client_manager must be set explicitly for custom db_driver.');

        $processor->processConfiguration($configuration, [[
            'db_driver' => 'custom',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
        ]]);
    }

    public function testShouldMakeAccessTokenManagerServiceMandatoryIfCustomDriverIsUsed()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "fos_oauth_server": The service access_token_manager must be set explicitly for custom db_driver.');

        $processor->processConfiguration($configuration, [[
            'db_driver' => 'custom',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
            'service' => [
                'client_manager' => 'a_client_manager_id',
            ],
        ]]);
    }

    public function testShouldMakeRefreshTokenManagerServiceMandatoryIfCustomDriverIsUsed()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "fos_oauth_server": The service refresh_token_manager must be set explicitly for custom db_driver.');

        $processor->processConfiguration($configuration, [[
            'db_driver' => 'custom',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
            'service' => [
                'client_manager' => 'a_client_manager_id',
                'access_token_manager' => 'anId',
            ],
        ]]);
    }

    public function testShouldMakeAuthCodeManagerServiceMandatoryIfCustomDriverIsUsed()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Invalid configuration for path "fos_oauth_server": The service auth_code_manager must be set explicitly for custom db_driver.');

        $processor->processConfiguration($configuration, [[
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
        ]]);
    }

    public function testShouldLoadCustomDriverConfig()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $config = $processor->processConfiguration($configuration, [[
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
        ]]);

        $this->assertArraySubset([
            'db_driver' => 'custom',
            'client_class' => 'aClientClass',
            'access_token_class' => 'anAccessTokenClass',
            'refresh_token_class' => 'aRefreshTokenClass',
            'auth_code_class' => 'anAuthCodeClass',
            'service' => [
                'storage' => 'fos_oauth_server.storage.default',
                'user_provider' => null,
                'client_manager' => 'a_client_manager_id',
                'access_token_manager' => 'an_access_token_manager_id',
                'refresh_token_manager' => 'a_refresh_token_manager_id',
                'auth_code_manager' => 'an_auth_code_manager_id',
            ],
        ], $config);
    }
}
