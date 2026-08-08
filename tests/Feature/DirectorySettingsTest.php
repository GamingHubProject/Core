<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Feature;

/* Host Azuriom integration coverage:
 - defaults resolve to Games / empty / 3 / compact / normal / all visibility true / compact fallback;
 - authorized save persists gaming-hub-core.directory.* settings;
 - unauthorized save is rejected;
 - title >120, description >2000, invalid columns/density/width/booleans/fallback are rejected;
 - directory description is escaped and line breaks are preserved;
 - enabled games remain ordered by sort_order, name, id.
These tests execute in the host Azuriom test suite because the plugin ZIP does not bundle core.
*/
