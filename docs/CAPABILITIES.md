# Capability flow

Capability resolution is server-scoped:

`Server -> enabled provider instances in position order -> registered provider types supporting the capability -> first available provider`.

The built-in Manual provider supports `server-status`. Game pages may display an aggregate status from the first ordered enabled/public server, but this is presentation only and is not a game-level capability resolver. Future provider plugins register reusable provider types and are assigned per server.
