<?php
namespace Azuriom\Plugin\GamingHubCore\Data;

final readonly class ProviderConfigurationField
{
    /** @param array<int, string> $options */
    public function __construct(
        public string $key,
        public string $label,
        public string $type = 'string',
        public bool $required = false,
        public array $options = [],
        public ?int $maxLength = null,
        public bool $secret = false,
        public ?string $help = null,
    ) {}

    public function detached(): self
    {
        return new self($this->key, $this->label, $this->type, $this->required, array_values($this->options), $this->maxLength, $this->secret, $this->help);
    }
}
