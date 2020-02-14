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

namespace FOS\OAuthServerBundle\Tests\Functional;

use LogicException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class TestCase extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir().'/FOSOAuthServerBundle/');
    }

    /**
     * Client response assertion of status code and response content.
     */
    protected function assertResponse(int $statusCode, string $content, bool $fullFailOutput = false): void
    {
        if (!($this->client instanceof KernelBrowser)) {
            throw new LogicException('Test attempts to check response, but client does not exist; use createClient() to set the test case client property.');
        }

        $this->assertSame(
            $statusCode,
            $this->client->getResponse()->getStatusCode(),
            sprintf('Failed asserting that response status code "%d" is "%d".', $this->client->getResponse()->getStatusCode(), $statusCode)
        );

        $responseContent = $this->client->getResponse()->getContent();

        if ('' === $responseContent && '' === $content) {
            $this->assertTrue(true);
            return;
        }

        if ('' === $responseContent) {
            $this->fail(sprintf('Response content is empty, expected "%s".', $content));
        } elseif ('' === $content) {

            // this differs from assertStringContainsString, which does not
            // fail on an empty string expectation
            $this->fail($fullFailOutput || strlen($responseContent) < 100
                ? sprintf('Failed asserting that response "%s" is empty.', $responseContent)
                : sprintf(
                    'Failed asserting that response "%s ... %s" is empty.',
                    substr($responseContent, 0, 40),
                    substr($responseContent, strlen($responseContent) - 40)
                )
            );
        }

        // not using assertStringContainsString to avoid full HTML doc in the
        // fail message
        if (mb_strpos($responseContent, $content) === false) {
            $this->fail($fullFailOutput || strlen($responseContent) < 100
                ? sprintf('Failed asserting that response "%s" contains "%s".', $responseContent, $content)
                : sprintf(
                    'Failed asserting that response "%s ... %s" contains "%s".',
                    substr($responseContent, 0, 40),
                    substr($responseContent, strlen($responseContent) - 40),
                    $content
                )
            );
        }
        $this->assertTrue(true);
    }
}
