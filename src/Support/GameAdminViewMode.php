<?php

namespace Azuriom\Plugin\GamingHubCore\Support;

final class GameAdminViewMode
{
    public const GRID = 'grid';
    public const LIST = 'list';

    public static function fromQuery(mixed $value): string
    {
        return $value === self::LIST ? self::LIST : self::GRID;
    }
}
