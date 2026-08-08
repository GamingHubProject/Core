<?php

namespace Azuriom\Plugin\GamingHubCore\Settings;

use Azuriom\Models\Setting;
use Illuminate\Support\Facades\Cache;

final class GamePageSettings
{
    public const PREFIX = 'gaming-hub-core.game-page.';
    public const DENSITIES = ['compact', 'standard'];
    public const COLUMNS = [1, 2, 3];
    public const ADDRESS_MODES = ['hidden', 'hostname', 'hostname_and_port'];

    public function all(): array
    {
        $density = $this->choice(self::PREFIX.'server_density', 'compact', self::DENSITIES);
        $columns = $this->choiceInt(self::PREFIX.'server_columns', 2, self::COLUMNS);
        $address = $this->choice(self::PREFIX.'show_address', 'hidden', self::ADDRESS_MODES);

        return [
            'show_servers' => $this->boolean(self::PREFIX.'show_servers', true),
            'server_density' => $density,
            'server_columns' => $columns,
            'show_server_descriptions' => $this->boolean(self::PREFIX.'show_server_descriptions', true),
            'show_status' => $this->boolean(self::PREFIX.'show_status', true),
            'show_provider_message' => $this->boolean(self::PREFIX.'show_provider_message', true),
            'show_join_button' => $this->boolean(self::PREFIX.'show_join_button', true),
            'show_address' => $address,
            'show_navigation' => $this->boolean(self::PREFIX.'show_navigation', true),
            'grid_class' => 'gh-server-grid--'.$columns,
            'card_class' => 'gh-server-card--'.$density,
        ];
    }

    public function save(array $values): void
    {
        Setting::updateSettings([
            self::PREFIX.'show_servers' => $this->inputBoolean($values, 'show_servers'),
            self::PREFIX.'server_density' => $values['server_density'],
            self::PREFIX.'server_columns' => (int) $values['server_columns'],
            self::PREFIX.'show_server_descriptions' => $this->inputBoolean($values, 'show_server_descriptions'),
            self::PREFIX.'show_status' => $this->inputBoolean($values, 'game_page_show_status'),
            self::PREFIX.'show_provider_message' => $this->inputBoolean($values, 'game_page_show_provider_message'),
            self::PREFIX.'show_join_button' => $this->inputBoolean($values, 'show_join_button'),
            self::PREFIX.'show_address' => $values['show_address'],
            self::PREFIX.'show_navigation' => $this->inputBoolean($values, 'show_game_navigation'),
        ]);

        Cache::forget('settings');
        Cache::forget('azuriom.settings');
    }

    private function boolean(string $key, bool $default): bool
    {
        $value = setting($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return $default;
    }

    private function inputBoolean(array $values, string $key): bool
    {
        if (! array_key_exists($key, $values)) {
            return false;
        }

        return filter_var($values[$key], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
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
