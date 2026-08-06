<?php

declare(strict_types=1);

namespace Illuminate\Validation {
    final class ValidationException extends \RuntimeException
    {
        /** @param array<string, list<string>> $errors */
        public function __construct(private array $errors)
        {
            parent::__construct('Validation failed.');
        }

        /** @param array<string, list<string>> $errors */
        public static function withMessages(array $errors): self
        {
            return new self($errors);
        }

        /** @return array<string, list<string>> */
        public function errors(): array
        {
            return $this->errors;
        }
    }
}

namespace Illuminate\Support\Facades {
    use Illuminate\Validation\ValidationException;

    final class Validator
    {
        /** @param array<string, mixed> $data @param array<string, list<string>> $rules */
        public static function make(array $data, array $rules): object
        {
            return new class($data, $rules) {
                /** @param array<string, mixed> $data @param array<string, list<string>> $rules */
                public function __construct(private array $data, private array $rules)
                {
                }

                /** @return array<string, mixed> */
                public function validate(): array
                {
                    $errors = [];
                    $validated = [];

                    foreach ($this->rules as $key => $rules) {
                        $present = array_key_exists($key, $this->data);
                        $value = $this->data[$key] ?? null;
                        if (in_array('required', $rules, true) && (! $present || $value === null || $value === '')) {
                            $errors[$key][] = "The {$key} field is required.";
                            continue;
                        }
                        if (! $present || $value === null || $value === '') {
                            continue;
                        }
                        foreach ($rules as $rule) {
                            if (str_starts_with($rule, 'in:') && ! in_array((string) $value, explode(',', substr($rule, 3)), true)) {
                                $errors[$key][] = "The selected {$key} is invalid.";
                            }
                            if ($rule === 'integer' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                                $errors[$key][] = "The {$key} must be an integer.";
                            }
                            if ($rule === 'boolean' && ! in_array($value, [true, false, 0, 1, '0', '1'], true)) {
                                $errors[$key][] = "The {$key} field must be true or false.";
                            }
                            if ($rule === 'string' && ! is_string($value)) {
                                $errors[$key][] = "The {$key} must be a string.";
                            }
                        }
                        $validated[$key] = $value;
                    }

                    if ($errors !== []) {
                        throw new ValidationException($errors);
                    }

                    return $validated;
                }
            };
        }
    }
}

namespace {
    use Azuriom\Plugin\GamingHubCore\Data\ProviderConfigurationField;
    use Azuriom\Plugin\GamingHubCore\Data\ProviderType;
    use Azuriom\Plugin\GamingHubCore\Validation\ProviderConfigurationValidator;
    use Illuminate\Validation\ValidationException;

    final class FakeRequest
    {
        /** @param array<string, mixed> $configuration */
        public function __construct(private array $configuration)
        {
        }

        public function input(string $key, mixed $default = null): mixed
        {
            return $key === 'configuration' ? $this->configuration : $default;
        }
    }

    final class FakeApplication
    {
        public function bound(string $key): bool
        {
            return $key === 'request';
        }
    }

    final class FakeTrace
    {
        /** @var array<string, mixed> */
        public array $last = [];

        /** @param array<string, mixed> $data */
        public function validated(array $data, string $source): void
        {
            $this->last = ['data' => $data, 'source' => $source];
        }
    }

    $fakeApplication = new FakeApplication();
    $fakeTrace = new FakeTrace();
    $fakeRequest = new FakeRequest([]);

    function app(?string $abstract = null): mixed
    {
        global $fakeApplication, $fakeTrace;

        return $abstract === null ? $fakeApplication : $fakeTrace;
    }

    function request(): FakeRequest
    {
        global $fakeRequest;

        return $fakeRequest;
    }

    $root = dirname(__DIR__);
    require $root.'/src/Data/ProviderConfigurationField.php';
    require $root.'/src/Data/ProviderType.php';
    require $root.'/src/Services/ProviderConfigurationInput.php';
    require $root.'/src/Validation/ProviderConfigurationValidator.php';

    $type = new ProviderType(
        'manual',
        'Manual',
        'Local status',
        'gaming-hub-core',
        'Gaming Hub Core',
        ['server-status'],
        [
            new ProviderConfigurationField('status', 'Status', 'select', true, ['online', 'offline', 'maintenance', 'unknown']),
            new ProviderConfigurationField('display_message', 'Message', 'string', false, [], 500),
        ],
    );

    $tests = 0;
    $failures = [];
    $check = static function (bool $condition, string $label) use (&$tests, &$failures): void {
        $tests++;
        if (! $condition) {
            $failures[] = $label;
        }
    };

    // Exact v0.6.5 failure input: extension validated() retained only its
    // transient mapping flag and dropped Manual's registered fields.
    $fakeRequest = new FakeRequest([
        'status' => 'online',
        'display_message' => 'Ready',
        'manual_identifier' => false,
    ]);
    $result = (new ProviderConfigurationValidator())->validate($type, ['manual_identifier' => false]);
    $check($result === ['status' => 'online', 'display_message' => 'Ready'], 'actual validator recovers and validates Manual registry fields');
    $check(! array_key_exists('manual_identifier', $result), 'actual validator excludes extension transient fields');
    $check(($fakeTrace->last['data']['provider_type'] ?? null) === 'manual', 'successful validation reaches trace');

    $fakeRequest = new FakeRequest(['display_message' => 'Missing status']);
    try {
        (new ProviderConfigurationValidator())->validate($type, []);
        $check(false, 'missing required status is rejected');
    } catch (ValidationException $exception) {
        $check(isset($exception->errors()['configuration.status']), 'validation error key matches provider Blade field');
        $check(! isset($exception->errors()['status']), 'unprefixed invisible error key is removed');
    }

    if ($failures !== []) {
        fwrite(STDERR, "FAILED ".count($failures)." / {$tests}\n - ".implode("\n - ", $failures)."\n");
        exit(1);
    }

    echo "PASS {$tests} provider validator behavior checks\n";
}
