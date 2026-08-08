<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class GamePageLayoutV043ContractTest extends TestCase
{
    public function test_nested_content_wrapper_is_absent(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/runtime/games/show-v043.blade.php');

        $this->assertStringContainsString('<div class="gh-game-page"', $view);
        $this->assertStringNotContainsString('class="content gh-game-page"', $view);
    }

    public function test_text_sections_have_natural_height_and_server_cards_keep_equal_height(): void
    {
        $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/runtime/games/show-v043.blade.php');

        $this->assertStringContainsString('.gh-game-section__card{height:auto}', $view);
        $this->assertStringContainsString('.gh-server-card{height:100%', $view);
        $this->assertStringNotContainsString('.gh-game-section__card{height:100%}', $view);
    }
}
