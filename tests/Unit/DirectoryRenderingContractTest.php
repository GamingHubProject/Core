<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

class DirectoryRenderingContractTest extends TestCase
{
    private string $view;

    protected function setUp(): void
    {
        $this->view = file_get_contents(__DIR__.'/../../resources/views/games/index.blade.php');
    }

    public function test_grid_contracts_are_rendered(): void
    {
        foreach ([1, 2, 3, 4] as $columns) {
            $this->assertStringContainsString('.gh-games-grid--'.$columns, $this->view);
        }
        $this->assertStringContainsString("{{ \$settings['grid_class'] }}", $this->view);
        $this->assertStringNotContainsString('col-xl-4', $this->view);
    }

    public function test_density_and_container_contracts_are_rendered(): void
    {
        $this->assertStringContainsString("{{ \$settings['card_class'] }}", $this->view);
        $this->assertStringContainsString("{{ \$settings['container_class'] }}", $this->view);
        foreach (['gh-page-container','gh-page-container-wide','gh-page-container-full'] as $class) {
            $this->assertStringContainsString('.'.$class, $this->view);
        }
    }

    public function test_no_banner_contract_omits_or_renders_media_deterministically(): void
    {
        $this->assertStringContainsString("trim(\$game->bannerUrl) !== ''", $this->view);
        $this->assertStringContainsString("\$showMedia = \$hasBanner || \$settings['fallback_style'] === 'media'", $this->view);
        $this->assertStringContainsString('@if($showMedia)', $this->view);
        $this->assertStringContainsString('data-gh-media="{{ $hasBanner ? \'banner\' : \'placeholder\' }}"', $this->view);
    }
}
