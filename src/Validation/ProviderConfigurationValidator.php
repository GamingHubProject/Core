<?php

namespace Azuriom\Plugin\GamingHubCore\Validation;

use Azuriom\Plugin\GamingHubCore\Data\ProviderType;
use Azuriom\Plugin\GamingHubCore\Services\ProviderConfigurationInput;
use Azuriom\Plugin\GamingHubCore\Services\ProviderCreationTrace;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class ProviderConfigurationValidator
{
    /**
     * Validates and returns only configuration keys declared by the registered
     * ProviderType. Extension-only transient fields are discarded.
     *
     * @param array<string, mixed> $configuration
     * @return array<string, mixed>
     */
    public function validate(ProviderType $type, array $configuration): array
    {
        $allowed = array_map(static fn ($field): string => $field->key, $type->fields);
        $raw = [];

        if (app()->bound('request')) {
            $candidate = request()->input('configuration', []);
            if (is_array($candidate)) {
                $raw = $candidate;
            }
        }

        $configuration = ProviderConfigurationInput::reconcile($configuration, $raw, $allowed);
        $rules = [];

        foreach ($type->fields as $field) {
            $rule = [$field->required ? 'required' : 'nullable'];
            $rule[] = match ($field->type) {
                'boolean' => 'boolean',
                'integer' => 'integer',
                default => 'string',
            };

            if ($field->maxLength !== null) {
                $rule[] = 'max:'.$field->maxLength;
            }
            if ($field->type === 'select') {
                $rule[] = 'in:'.implode(',', $field->options);
            }

            $rules[$field->key] = $rule;
        }

        try {
            $validated = Validator::make($configuration, $rules)->validate();
        } catch (ValidationException $exception) {
            $prefixed = [];
            foreach ($exception->errors() as $key => $messages) {
                $prefixed['configuration.'.$key] = $messages;
            }

            throw ValidationException::withMessages($prefixed);
        }

        app(ProviderCreationTrace::class)->validated([
            'provider_type' => $type->id,
            'configuration' => $validated,
        ], self::class);

        return $validated;
    }
}
