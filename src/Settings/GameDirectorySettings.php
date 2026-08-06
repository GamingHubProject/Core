<?php

namespace Azuriom\Plugin\GamingHubCore\Settings;

use Azuriom\Models\Setting;
use Illuminate\Support\Facades\Cache;

final class GameDirectorySettings
{
    public const PREFIX = 'gaming-hub-core.directory.';
    public const COLUMNS = [1, 2, 3, 4];
    public const DENSITIES = ['compact', 'standard'];
    public const WIDTHS = ['normal', 'wide', 'full'];
    public const FALLBACKS = ['compact', 'media'];

    public function all(): array
    {
        $columns = $this->choiceInt(self::PREFIX.'columns', 3, self::COLUMNS);
        $density = $this->choice(self::PREFIX.'density', 'compact', self::DENSITIES);
        $containerWidth = $this->choice(self::PREFIX.'container_width', 'normal', self::WIDTHS);
        $fallbackStyle = $this->choice(self::PREFIX.'fallback_style', 'compact', self::FALLBACKS);

        return [
            'title' => (string) setting(self::PREFIX.'title', 'Games'),
            'description' => (string) setting(self::PREFIX.'description', ''),
            'columns' => $columns,
            'density' => $density,
            'container_width' => $containerWidth,
            'show_description' => (bool) setting(self::PREFIX.'show_description', true),
            'show_status' => (bool) setting(self::PREFIX.'show_status', true),
            'show_provider_message' => (bool) setting(self::PREFIX.'show_provider_message', true),
            'show_button' => (bool) setting(self::PREFIX.'show_button', true),
            'show_count' => (bool) setting(self::PREFIX.'show_count', true),
            'show_servers_on_game_page' => $this->showServersOnGamePage(),
            'fallback_style' => $fallbackStyle,
            // Canonical rendering contract. Views and themes consume these values,
            // rather than rebuilding setting-to-class mappings independently.
            'grid_class' => 'gh-games-grid--'.$columns,
            'card_class' => 'gh-game-card--'.$density,
            'container_class' => match ($containerWidth) {
                'wide' => 'gh-page-container-wide',
                'full' => 'gh-page-container-full',
                default => 'gh-page-container',
            },
            'fallback_class' => 'gh-games-fallback--'.($fallbackStyle === 'media' ? 'media' : 'compact'),
        ];
    }

    public function save(array $values): void
    {
        Setting::updateSettings([
            self::PREFIX.'title' => $values['title'],
            self::PREFIX.'description' => $values['description'] ?? '',
            self::PREFIX.'columns' => (int) $values['columns'],
            self::PREFIX.'density' => $values['density'],
            self::PREFIX.'container_width' => $values['container_width'],
            self::PREFIX.'show_description' => (bool) ($values['show_description'] ?? false),
            self::PREFIX.'show_status' => (bool) ($values['show_status'] ?? false),
            self::PREFIX.'show_provider_message' => (bool) ($values['show_provider_message'] ?? false),
            self::PREFIX.'show_button' => (bool) ($values['show_button'] ?? false),
            self::PREFIX.'show_count' => (bool) ($values['show_count'] ?? false),
            self::PREFIX.'fallback_style' => $values['fallback_style'],
            self::PREFIX.'show_servers_on_game_page' => (bool) ($values['show_servers_on_game_page'] ?? false),
        ]);

        // Azuriom normally invalidates its settings cache in updateSettings().
        // These guards also cover installations using a cached settings helper.
        Cache::forget('settings');
        Cache::forget('azuriom.settings');
    }

    public function showServersOnGamePage(): bool
    {
        return (bool) setting(self::PREFIX.'show_servers_on_game_page', true);
    }

    private function choice(string $key, string $default, array $allowed): string
    {
        $value = (string) setting($key, $default);

        return in_array($value, $allowed, true) ? $value : $default;
    }

    private function choiceInt(string $key, int $default, array $allowed): int
    {
        $value = (int) setting($key, $default);

        return in_array($value, $allowed, true) ? $value : $default;
    }
}
