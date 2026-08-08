# Native navbar targets

Core exposes the Games directory, each enabled game, and each enabled/public server through Azuriom's plugin-link route descriptions. Server labels use `Games -> Game -> Server`; administrators may replace the displayed label through Azuriom's navbar editor. Routes are stable UUID-based aliases that resolve the current game/server slugs. Disabled, private, or deleted servers are no longer registered and stale Azuriom links fall back through Azuriom's normal missing-route behavior. No custom navigation system is used.
