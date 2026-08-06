<?php

namespace Azuriom\Plugin\GamingHubCore\Events;

use Azuriom\Plugin\GamingHubCore\Models\Game;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class GameEnabled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Game $game) {}
}
