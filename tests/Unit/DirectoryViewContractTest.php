<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

class DirectoryViewContractTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = file_get_contents(__DIR__.'/../../resources/views/games/index.blade.php');
    }

    public function test_all_column_contracts_exist(): void
    {
        foreach (["1 => 'col-12'", "2 => 'col-12 col-md-6'", "3 => 'col-12 col-md-6 col-xl-4'", "4 => 'col-12 col-sm-6 col-xl-3'"] as $contract) {
            self::assertStringContainsString($contract, $this->view);
        }
    }

    public function test_compact_fallback_does_not_require_media(): void
    {
        self::assertStringContainsString("\$showMedia = \$game->bannerUrl || \$settings['fallback_style'] === 'media'", $this->view);
    }

    public function test_description_is_escaped_before_line_breaks(): void
    {
        self::assertStringContainsString("nl2br(e(\$settings['description']))", $this->view);
    }
}
