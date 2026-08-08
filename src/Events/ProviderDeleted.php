<?php

namespace Azuriom\Plugin\GamingHubCore\Events;

use Azuriom\Plugin\GamingHubCore\Models\ProviderInstance;
use Illuminate\Foundation\Events\Dispatchable;

final class ProviderDeleted
{
    use Dispatchable;

    public function __construct(public readonly ProviderInstance $provider)
    {
    }
}
