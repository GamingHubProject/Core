<?php

namespace Azuriom\Plugin\GamingHubCore\Navigation;

use Azuriom\Plugin\GamingHubCore\Models\Game;
use Closure;
use Illuminate\Http\Request;

final readonly class GameNavigationItem
{
    /**
     * @param Closure(Game): string $url
     * @param Closure(Game, Request): bool|null $visible
     * @param Closure(Game, Request): bool|null $active
     */
    public function __construct(
        public string $id,
        public string $label,
        public Closure $url,
        public ?string $icon = null,
        public int $order = 100,
        public ?Closure $visible = null,
        public ?Closure $active = null,
    ) {
    }
}
