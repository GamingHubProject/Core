<?php

namespace Azuriom\Plugin\GamingHubCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gaminghub.games.manage') === true;
    }

    public function rules(): array
    {
        $gameId = $this->route('game')?->getKey();

        return [
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:64'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash:ascii', Rule::unique('gaminghub_games', 'slug')->ignore($gameId)],
            'description' => ['nullable', 'string', 'max:10000'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'long_description' => ['nullable', 'string', 'max:20000'],
            'icon_url' => ['nullable', 'url:http,https', 'max:2048'],
            'banner_url' => ['nullable', 'url:http,https', 'max:2048'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'enabled' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:-2147483648', 'max:2147483647'],
            'icon_media_id' => ['nullable', 'integer', 'min:1'],
            'cover_media_id' => ['nullable', 'integer', 'min:1'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => is_string($this->slug) ? strtolower(trim($this->slug)) : $this->slug,
            'enabled' => $this->boolean('enabled'),
            'icon_media_id' => $this->filled('icon_media_id') ? $this->icon_media_id : null,
            'cover_media_id' => $this->filled('cover_media_id') ? $this->cover_media_id : null,
            'short_description' => $this->filled('short_description') ? trim((string) $this->short_description) : null,
            'long_description' => $this->filled('long_description') ? trim((string) $this->long_description) : null,
            'icon_url' => $this->filled('icon_url') ? trim((string) $this->icon_url) : null,
            'banner_url' => $this->filled('banner_url') ? trim((string) $this->banner_url) : null,
        ]);
    }
}
