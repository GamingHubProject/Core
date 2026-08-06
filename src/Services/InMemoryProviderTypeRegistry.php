<?php
namespace Azuriom\Plugin\GamingHubCore\Services;

use Azuriom\Plugin\GamingHubCore\Contracts\ProviderTypeRegistry;
use Azuriom\Plugin\GamingHubCore\Data\ProviderConfigurationField;
use Azuriom\Plugin\GamingHubCore\Data\ProviderType;
use Azuriom\Plugin\GamingHubCore\Exceptions\DuplicateProviderType;
use Azuriom\Plugin\GamingHubCore\Exceptions\InvalidProviderType;
use Azuriom\Plugin\GamingHubCore\Exceptions\UnknownProviderType;

final class InMemoryProviderTypeRegistry implements ProviderTypeRegistry
{
    public const CAPABILITIES = ['server-status','players','player-identities','player-positions','chat','commands','configuration','metrics'];
    /** @var array<string, ProviderType> */ private array $types = [];

    public function register(ProviderType $type): void
    {
        $this->validate($type);
        if (isset($this->types[$type->id])) throw new DuplicateProviderType("Provider type [{$type->id}] is already registered.");
        $this->types[$type->id] = $type->detached();
    }
    public function get(string $id): ProviderType
    {
        return $this->find($id) ?? throw new UnknownProviderType("Unknown provider type [{$id}].");
    }
    public function find(string $id): ?ProviderType { return isset($this->types[$id]) ? $this->types[$id]->detached() : null; }
    public function all(): array { return array_map(fn (ProviderType $type) => $type->detached(), array_values($this->types)); }

    private function validate(ProviderType $type): void
    {
        if (! preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/', $type->id)) throw new InvalidProviderType('Provider type IDs must be lowercase kebab-case.');
        if (! preg_match('/^[a-z0-9][a-z0-9-]*$/', $type->pluginId) || trim($type->pluginName) === '') throw new InvalidProviderType('Owning plugin information is invalid.');
        if (trim($type->name) === '' || trim($type->description) === '') throw new InvalidProviderType('Provider name and description are required.');
        if ($type->capabilities === [] || count($type->capabilities) !== count(array_unique($type->capabilities))) throw new InvalidProviderType('Capabilities must be non-empty and unique.');
        foreach ($type->capabilities as $capability) if (! in_array($capability, self::CAPABILITIES, true)) throw new InvalidProviderType("Unsupported capability [{$capability}].");
        $keys = [];
        foreach ($type->fields as $field) {
            if (! $field instanceof ProviderConfigurationField || ! preg_match('/^[a-z][a-z0-9_]*$/', $field->key) || isset($keys[$field->key])) throw new InvalidProviderType('Configuration fields must have unique snake_case keys.');
            if (! in_array($field->type, ['string','select','boolean','integer'], true)) throw new InvalidProviderType("Unsupported configuration field type [{$field->type}].");
            if ($field->type === 'select' && $field->options === []) throw new InvalidProviderType("Select field [{$field->key}] requires options.");
            $keys[$field->key] = true;
        }
    }
}
