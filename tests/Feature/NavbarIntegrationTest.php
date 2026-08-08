<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Feature;

/* Host-Azuriom integration coverage:
 - Gaming Hub Core appears in Administration -> Navbar -> Add Link -> Plugin.
 - Games is a selectable target backed by gaming-hub-core.games.index.
 - saved links use Azuriom route generation and respect the configured base URL.
 - the standard navbar item is active on /games and /games/{slug}.
 - administrator-selected label, position, parent and visibility are preserved.
 - disabling Gaming Hub Core removes its registered route target safely.
 - themes rendering Azuriom's standard navbar links display the configured item.
The distributed ZIP has no standalone Azuriom application bootstrap; these
scenarios run in the host repository test suite.
*/
