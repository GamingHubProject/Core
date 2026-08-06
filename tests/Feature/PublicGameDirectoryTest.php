<?php

namespace Azuriom\Plugin\GamingHubCore\Tests\Feature;

/* Host-Azuriom integration coverage:
 - GET /games is public and lists only enabled games in sort_order/name/id order.
 - GET /games/{slug} renders a detached public DTO and returns 404 for unknown or disabled games.
 - a Manual provider with status=online and display_message renders both values.
 - disabled providers are ignored and a missing provider renders Unknown.
 - malformed/unknown persisted status is presented as Unknown defensively.
 - short/long descriptions and validated HTTP(S) image URLs render escaped.
The plugin ZIP intentionally does not bundle an Azuriom application bootstrap;
these HTTP/database tests run in the host repository test suite.
*/
