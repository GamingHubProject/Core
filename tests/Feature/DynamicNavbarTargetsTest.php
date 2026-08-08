<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Feature;

/* Host Azuriom integration coverage:
 - each enabled game contributes a selectable UUID-backed plugin route;
 - disabled games are excluded;
 - renaming a slug changes the generated URL while preserving the saved route name;
 - deleting or disabling removes the route and Azuriom's NavbarElement::getLink returns '#';
 - direct game target opens the existing detail action and preserves 404/status/provider behavior;
 - individual route names activate only their own UUID route group;
 - gaming-hub-core.games.index remains registered.
*/
