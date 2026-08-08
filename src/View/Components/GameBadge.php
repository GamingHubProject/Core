<?php

namespace Azuriom\Plugin\GamingHubCore\View\Components;

use Azuriom\Plugin\GamingHubCore\Models\Game;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GameBadge extends Component
{
    public function __construct(public readonly Game $game) {}

    public function render(): View
    {
        return view('gaming-hub-core::components.game-badge');
    }
}
