# Extension examples

## Future integration plugin

Register a Provider Type, then register capability readers during boot. Readers own authentication, API clients and provider-specific response translation.

## Future Maps consumer

Resolve `player-positions` through `SharedDataGateway`. The Maps plugin depends only on normalized results and does not import concrete game or panel clients.

## Future Marketplace consumer

Use `player-identities` and future namespaced item capabilities through the internal gateway. Public output must pass through public policies.

Core includes no external game API clients, scheduled polling, queues, public JSON API, package lifecycle manager, maps, marketplace, or game-specific integrations.
