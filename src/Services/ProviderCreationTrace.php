<?php

namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Models\ProviderInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ProviderCreationTrace
{
    public function enabled(): bool
    {
        return filter_var(config('gaming-hub-core.providers.trace_creation', false), FILTER_VALIDATE_BOOL);
    }

    /** @param array<string, mixed> $context */
    public function stage(string $stage, array $context = []): void
    {
        if (! $this->enabled()) {
            return;
        }

        Log::info('Gaming Hub provider creation: '.$stage, $this->sanitize($context));
    }

    public function request(Request $request): void
    {
        $configuration = $request->input('configuration', []);

        $this->stage('request_received', [
            'route' => $request->route()?->getName(),
            'game_id' => $this->routeKey($request, 'game'),
            'server_id' => $this->routeKey($request, 'server'),
            'provider_type' => $request->input('provider_type'),
            'name' => $request->input('name'),
            'enabled' => $request->input('enabled'),
            'position' => $request->input('position'),
            'configuration_keys' => is_array($configuration) ? array_keys($configuration) : [],
            'has_client_token_override' => filled($request->input('client_token_override')),
        ]);
    }

    /** @param array<string, mixed> $data */
    public function validated(array $data, string $source): void
    {
        $configuration = $data['configuration'] ?? [];

        $this->stage('validated_payload', [
            'source' => $source,
            'provider_type' => $data['provider_type'] ?? null,
            'name' => $data['name'] ?? null,
            'enabled' => $data['enabled'] ?? null,
            'position' => $data['position'] ?? null,
            'configuration_keys' => is_array($configuration) ? array_keys($configuration) : [],
        ]);
    }

    public function model(string $stage, ProviderInstance $provider): void
    {
        $this->stage($stage, [
            'provider_id' => $provider->exists ? $provider->getKey() : null,
            'game_id' => $provider->game_id,
            'server_id' => $provider->server_id,
            'provider_type' => $provider->provider_type,
            'name' => $provider->name,
            'enabled' => $provider->enabled,
            'position' => $provider->position,
            'configuration_keys' => array_keys((array) $provider->configuration),
        ]);
    }

    /** @param array<string, array<int, string>> $errors */
    public function validationFailed(array $errors): void
    {
        $this->stage('validation_failed', [
            'errors' => $errors,
        ]);
    }

    public function failed(Throwable $exception, string $stage): void
    {
        if (! $this->enabled()) {
            return;
        }

        Log::error('Gaming Hub provider creation failed at '.$stage, [
            'exception' => $exception::class,
            'message' => $this->safeMessage($exception->getMessage()),
        ]);
    }

    private function routeKey(Request $request, string $parameter): mixed
    {
        $value = $request->route($parameter);

        return is_object($value) && method_exists($value, 'getKey')
            ? $value->getKey()
            : $value;
    }

    private function safeMessage(string $message): string
    {
        $message = preg_replace('/Bearer\s+[A-Za-z0-9._~+\/=-]+/i', 'Bearer [redacted]', $message) ?? $message;
        $message = preg_replace(
            '/([A-Za-z0-9_.-]*(?:token|password|secret|authorization|credential|api[_-]?key)[A-Za-z0-9_.-]*)(\s*[=:]\s*)(?:\"[^\"]*\"|\'[^\']*\'|[^\s,;]+)/i',
            '$1$2[redacted]',
            $message,
        ) ?? $message;
        $message = preg_replace(
            '/(api\s+key)(\s*[=:]\s*)(?:\"[^\"]*\"|\'[^\']*\'|[^\s,;]+)/i',
            '$1$2[redacted]',
            $message,
        ) ?? $message;
        $message = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $message) ?? $message;

        return substr($message, 0, 1000);
    }

    /** @param array<string, mixed> $context @return array<string, mixed> */
    private function sanitize(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $sanitized[$key] = '[redacted]';

                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->safeMessage($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', '', $key) ?? $key);

        return preg_match('/token|password|secret|apikey|authorization|credential/', $normalized) === 1;
    }
}
