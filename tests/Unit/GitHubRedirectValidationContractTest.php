<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class GitHubRedirectValidationContractTest extends TestCase
{
    public function test_github_hosts_and_redirect_limit_are_explicit(): void
    {
        $source = file_get_contents(__DIR__.'/../../src/Services/SafeExtensionHttpClient.php');

        foreach ([
            'github.com',
            'api.github.com',
            'objects.githubusercontent.com',
            'release-assets.githubusercontent.com',
            'raw.githubusercontent.com',
            'githubusercontent.com',
        ] as $host) {
            self::assertStringContainsString("'{$host}'", $source);
        }

        self::assertStringContainsString("scheme !== 'https'", $source);
        self::assertStringContainsString('github_redirect_limit', $source);
        self::assertStringContainsString('protocol downgrade', $source);
    }
}
