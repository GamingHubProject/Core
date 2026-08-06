# Public services

`GameRegistry` remains available as `gaminghub.games`.

`ProviderTypeRegistry` (alias `gaminghub.provider-types`) registers and retrieves immutable/detached provider metadata through `register()`, `get()`, `find()`, and `all()`.

`ProviderInstances` (alias `gaminghub.providers`) returns `ProviderInstanceData` DTOs through `forGame()`, `findForGame()`, `enabledForGameByCapability()`, and `validatedConfiguration()`.
