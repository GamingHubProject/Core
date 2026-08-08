# Limitations

- Directory introduction formatting is safe plain text with preserved line breaks; no custom HTML or page builder is provided.
- Azuriom plugin navbar targets store route names but no route parameters. Gaming Hub Core therefore generates stable per-game named routes keyed by UUID.
- Azuriom's native prefix-based active detection can also mark the existing Games directory target active on detail routes. The plugin does not override core navbar behavior.
- Host-integrated HTTP/database tests require an Azuriom application test harness; the distributable plugin does not bundle Azuriom core.
