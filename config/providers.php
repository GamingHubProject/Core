<?php

return [
    /*
     * Optional sanitized provider-creation diagnostics. Production packages
     * keep this disabled unless an administrator explicitly opts in through
     * GAMING_HUB_PROVIDER_TRACE.
     */
    'trace_creation' => filter_var(env('GAMING_HUB_PROVIDER_TRACE', false), FILTER_VALIDATE_BOOL),
];
