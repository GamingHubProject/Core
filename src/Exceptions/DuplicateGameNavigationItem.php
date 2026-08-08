<?php

namespace Azuriom\Plugin\GamingHubCore\Exceptions;

use LogicException;

final class DuplicateGameNavigationItem extends LogicException
{
    public static function forId(string $id): self
    {
        return new self("A Gaming Hub game navigation item with ID [{$id}] is already registered.");
    }
}
