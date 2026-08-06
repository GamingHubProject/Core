# Shared Data Gateway

Gaming Hub Core v0.5.0 exposes `SharedDataGateway` through Laravel's container and the `gaminghub.shared-data` alias.

```php
$result = app(SharedDataGateway::class)->read($server, 'server-status');
$public = app(SharedDataGateway::class)->publicRead($server, 'server-status');
$many = app(SharedDataGateway::class)->readMany($server, ['server-status', 'metrics']);
```

`read()` is an internal backend contract. It returns detached normalized data plus internal provider identifiers and safe diagnostics. It is not exposed as an HTTP endpoint. `publicRead()` applies statistic visibility and attribution policies and removes provider identifiers and diagnostics.

Result states are `available`, `unavailable`, `unsupported`, `stale`, and `failed`. Provider exceptions are normalized to safe error categories. Raw responses, credentials, headers and configuration are never the primary result API.
