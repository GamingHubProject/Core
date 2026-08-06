<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Feature;

/*
Host Azuriom integration coverage for v0.6.6:

Creation and update
- create Manual, Pelican, Pterodactyl, and extension-owned providers;
- create multiple instances of the same provider type;
- create after deletion;
- extension controllers using ProviderInstance directly receive append priority;
- extension ordinary updates cannot overwrite server ownership or priority;
- registry-declared configuration is persisted and transient form keys are discarded.

Deletion and ordering
- every Core create/delete/move executes in a transaction with Server/provider row locks;
- positions remain a unique one-based contiguous sequence per Server;
- move swaps only the immediate previous/next provider;
- boundary movement returns a safe validation response;
- deletion normalizes remaining positions;
- another Server is never reordered;
- the unique `(server_id, position)` index prevents ambiguous persisted priority.

Capability priority
- the first enabled registered provider supporting the capability is selected;
- unsupported capabilities and disabled providers are skipped;
- `position, id` is deterministic during legacy normalization.

Persistence
- model reload, cache clearing, and Docker/container restart retain database priority.

The distributed plugin ZIP does not contain a standalone Azuriom application
bootstrap. `tests/run-provider-lifecycle.php` and
`tests/run-provider-crud-v065.php` and `tests/run-provider-creation-v066.php` provide executable package-level coverage;
these database/HTTP scenarios run in the host Azuriom test suite.
*/
