# Domain events

Gaming Hub Core dispatches standard Laravel events after successful administration changes:

- `GameCreated`
- `GameUpdated`
- `GameDeleted`
- `GameEnabled`
- `GameDisabled`

Each event exposes a public readonly `Game $game`. Consumers should register normal Laravel listeners in their own plugin service provider. No custom event bus is included.
