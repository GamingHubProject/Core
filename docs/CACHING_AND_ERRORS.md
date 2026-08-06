# Caching, freshness and errors

Readers declare a cache TTL. The Manual reader uses no cache. Cache keys include Server ID, provider instance ID, capability and provider `updated_at`, so configuration saves or enable/disable updates select a new key. Deleted or disabled providers are no longer selected. Policy changes filter existing data and do not refetch providers.

Readers may explicitly return a `stale` result. Cache failures fall back to direct reads.

Normalized errors: `authentication_failed`, `connection_failed`, `timeout`, `invalid_response`, `configuration_invalid`, `unsupported`, `unavailable`, and `unknown_error`. Public results exclude raw exceptions and administrator diagnostics.
