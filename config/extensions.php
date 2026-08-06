<?php
return [
    'official_registry_url' => env('GAMING_HUB_OFFICIAL_REGISTRY_URL', 'https://raw.githubusercontent.com/gaming-hub-community/registry/main/extensions.json'),
    'allow_private_hosts' => (bool) env('GAMING_HUB_EXTENSIONS_ALLOW_PRIVATE_HOSTS', false),
    'registry_cache_ttl' => 300,
    'http_timeout' => 10,
    'download_timeout' => 30,
    'github_redirect_limit' => 5,
    'max_download_bytes' => 50 * 1024 * 1024,
    'max_extracted_bytes' => 150 * 1024 * 1024,
    'max_files' => 5000,
    'stale_staging_hours' => 24,
    'operation_log_retention_days' => 180,
    'allow_official_without_checksum' => false,
    'retain_successful_update_backups' => (bool) env('GAMING_HUB_EXTENSIONS_RETAIN_UPDATE_BACKUPS', true),
];
