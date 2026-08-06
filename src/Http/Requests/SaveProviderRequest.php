<?php

namespace Azuriom\Plugin\GamingHubCore\Http\Requests;

use Azuriom\Plugin\GamingHubCore\Contracts\ProviderTypeRegistry;
use Azuriom\Plugin\GamingHubCore\Validation\ProviderConfigurationValidator;
use Azuriom\Plugin\GamingHubCore\Services\ProviderCreationTrace;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('gaminghub.providers.manage') === true;
    }

    public function rules(): array
    {
        $types = app(ProviderTypeRegistry::class);
        $type = $types->find((string) $this->input('provider_type'));
        $availableIds = array_map(
            static fn ($candidate): string => $candidate->id,
            array_filter($types->all(), static fn ($candidate): bool => $candidate->available),
        );

        $rules = [
            'provider_type' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', Rule::in($availableIds)],
            'name' => ['required', 'string', 'max:255'],
            'enabled' => ['required', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'configuration' => ['array'],
        ];

        if ($type !== null && $type->available) {
            foreach ($type->fields as $field) {
                $fieldRules = [$field->required ? 'required' : 'nullable'];
                $fieldRules[] = match ($field->type) {
                    'boolean' => 'boolean',
                    'integer' => 'integer',
                    default => 'string',
                };

                if ($field->maxLength !== null) {
                    $fieldRules[] = 'max:'.$field->maxLength;
                }
                if ($field->type === 'select') {
                    $fieldRules[] = Rule::in($field->options);
                }

                $rules['configuration.'.$field->key] = $fieldRules;
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = app(ProviderTypeRegistry::class)->find((string) $this->input('provider_type'));
            if ($type === null) {
                $validator->errors()->add('provider_type', trans('gaming-hub-core::admin.validation.unknown_type'));

                return;
            }
            if (! $type->available) {
                $validator->errors()->add('provider_type', trans('gaming-hub-core::admin.validation.unavailable_type'));

                return;
            }

            $configuration = $this->input('configuration', []);
            if (! is_array($configuration) || ($configuration !== [] && array_is_list($configuration))) {
                $validator->errors()->add('configuration', trans('gaming-hub-core::admin.validation.configuration_object'));
            }
        });
    }

    /** @return array<string, mixed> */
    public function providerData(): array
    {
        $data = $this->validated();
        $type = app(ProviderTypeRegistry::class)->get((string) $data['provider_type']);
        $data['configuration'] = app(ProviderConfigurationValidator::class)->validate(
            $type,
            (array) ($data['configuration'] ?? []),
        );
        app(ProviderCreationTrace::class)->validated($data, self::class);

        return $data;
    }

    protected function prepareForValidation(): void
    {
        $configuration = $this->input('configuration', []);
        if (! is_array($configuration)) {
            $configuration = [];
        }

        $type = app(ProviderTypeRegistry::class)->find((string) $this->input('provider_type'));
        if ($type !== null) {
            foreach ($type->fields as $field) {
                if ($field->type === 'boolean') {
                    $configuration[$field->key] = $this->boolean('configuration.'.$field->key);
                }
            }
        }

        $this->merge([
            'enabled' => $this->boolean('enabled'),
            'configuration' => $configuration,
        ]);
    }
}
