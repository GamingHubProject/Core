<?php

return [
    /*
     * Temporary, sanitized provider creation trace. It never writes provider
     * configuration values or credentials. Disable after runtime verification.
     */
    'trace_creation' => (bool) env('GAMING_HUB_PROVIDER_TRACE', true),
];
