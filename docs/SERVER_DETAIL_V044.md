# Server Detail rendering contract (v0.4.4)

The public Server Detail controller validates the nested resource through the resolved Game relationship. It passes only normalized presentation data to the private runtime view:

- `currentGame`: required `PublicGameData`
- `currentServer`: required `PublicServerData`
- `gamePageSettings`: normalized Game Page settings
- `gameNavigation`: resolved visible navigation entries

The active theme may style the semantic `gh-*` classes but cannot replace the private `gaming-hub-core-runtime-v044` view namespace. Optional icon, banner, provider message, address, port, and join URL values are intentionally omitted or given a visual fallback. A missing provider produces status `unknown`.
