<?php
namespace Azuriom\Plugin\GamingHubCore\Data;

final readonly class ProviderType
{
    /** @param array<int, string> $capabilities @param array<int, ProviderConfigurationField> $fields */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $pluginId,
        public string $pluginName,
        public array $capabilities,
        public array $fields,
        public bool $available = true,
        public ?string $publicAttributionLabel = null,
    ) {}

    public function supports(string $capability): bool { return in_array($capability, $this->capabilities, true); }
    public function detached(): self
    {
        return new self($this->id, $this->name, $this->description, $this->pluginId, $this->pluginName, array_values($this->capabilities), array_map(fn ($field) => $field->detached(), $this->fields), $this->available, $this->publicAttributionLabel);
    }
}
