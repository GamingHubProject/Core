<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Models\ExtensionSource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class ExtensionSourceManager
{
    public function __construct(
        private SafeExtensionHttpClient $http,
        private ExtensionRegistryValidator $validator,
        private GitHubReleaseClient $github,
        private ExtensionSafeMessage $messages,
    ) {
    }

    public function ensureOfficial(): ExtensionSource
    {
        return ExtensionSource::firstOrCreate(['source_id' => 'gaming-hub-official'], [
            'type' => 'official',
            'name' => 'Official Gaming Hub Registry',
            'url' => (string) config('gaming-hub-core.extensions.official_registry_url'),
            'trust_level' => 'official',
            'trusted' => true,
            'enabled' => true,
        ]);
    }

    public function refresh(ExtensionSource $source, bool $force = false): array
    {
        $key = 'gaminghub:extensions:source:'.$source->id;
        if (! $force && ($cached = Cache::get($key))) {
            return $cached;
        }

        try {
            if ($source->type === 'github') {
                $release = $this->github->latest($source->url, $source->allow_prereleases);
                $data = ['kind' => 'github', 'release' => $release];
            } else {
                $raw = $this->http->json($source->url, $source->allow_private_host);
                $registry = $this->validator->validate(
                    $raw,
                    $source->type === 'official',
                    $source->allow_private_host,
                );
                $data = ['kind' => 'registry', 'registry' => $registry];
            }

            Cache::put($key, $data, (int) config('gaming-hub-core.extensions.registry_cache_ttl', 300));
            $source->forceFill([
                'last_successful_refresh_at' => now(),
                'last_error' => null,
            ])->save();

            return $data;
        } catch (\Throwable $exception) {
            $source->forceFill([
                'last_error' => $this->messages->fromThrowable($exception),
            ])->save();

            if ($cached = Cache::get($key)) {
                return $cached + ['stale' => true, 'error' => $source->last_error];
            }

            throw $exception;
        }
    }

    public function invalidate(ExtensionSource $source): void
    {
        Cache::forget('gaminghub:extensions:source:'.$source->id);
    }

    public function makeId(string $name): string
    {
        return 'custom-'.Str::slug($name).'-'.Str::lower(Str::random(6));
    }
}
