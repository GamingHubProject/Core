# Server Registry

Gaming Hub Core v0.4.0 uses `Game -> Server -> ProviderInstance`. Servers are the primary runtime object. A game can own any number of ordered servers, and every server can own multiple reusable provider instances.

Server fields cover public presentation and future connection metadata: hostname, display port and join URL. They do not initiate network connections. Public routes require the game and server to be enabled and the server to be public.

Deleting a game cascades to its servers and provider instances. Deleting a server cascades only to that server's providers. Duplicating a server copies metadata only.
