@csrf
@if(isset($game))
    @method('PUT')
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <section class="mb-4" aria-labelledby="game-identity-heading">
            <h3 class="h5" id="game-identity-heading">{{ trans('gaming-hub-core::admin.games.sections.identity') }}</h3>
            <p class="text-muted small">{{ trans('gaming-hub-core::admin.games.sections.identity_help') }}</p>

            <div class="mb-3">
                <label class="form-label" for="name">{{ trans('gaming-hub-core::admin.fields.name') }}</label>
                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" required value="{{ old('name', $game->name ?? '') }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="short_name">{{ trans('gaming-hub-core::admin.fields.short_name') }}</label>
                    <input class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" maxlength="64" required value="{{ old('short_name', $game->short_name ?? '') }}">
                    @error('short_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="slug">{{ trans('gaming-hub-core::admin.fields.slug') }}</label>
                    <input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" required value="{{ old('slug', $game->slug ?? '') }}">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <hr>

        <section class="my-4" aria-labelledby="game-presentation-heading">
            <h3 class="h5" id="game-presentation-heading">{{ trans('gaming-hub-core::admin.games.sections.presentation') }}</h3>
            <p class="text-muted small">{{ trans('gaming-hub-core::admin.games.sections.presentation_help') }}</p>

            <div class="mb-3">
                <label class="form-label" for="short_description">{{ trans('gaming-hub-core::admin.fields.short_description') }}</label>
                <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2" maxlength="500">{{ old('short_description', $game->short_description ?? '') }}</textarea>
                @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="long_description">{{ trans('gaming-hub-core::admin.fields.long_description') }}</label>
                <textarea class="form-control @error('long_description') is-invalid @enderror" id="long_description" name="long_description" rows="7">{{ old('long_description', $game->long_description ?? '') }}</textarea>
                @error('long_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            @if(isset($game) && filled($game->description) && (blank($game->short_description) || blank($game->long_description)))
                <div class="alert alert-secondary py-2 small" role="note">
                    {{ trans('gaming-hub-core::admin.games.legacy_description_help') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="icon_url">{{ trans('gaming-hub-core::admin.fields.icon_url') }}</label>
                    <input class="form-control @error('icon_url') is-invalid @enderror" type="url" id="icon_url" name="icon_url" maxlength="2048" value="{{ old('icon_url', $game->icon_url ?? '') }}">
                    @error('icon_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="banner_url">{{ trans('gaming-hub-core::admin.fields.banner_url') }}</label>
                    <input class="form-control @error('banner_url') is-invalid @enderror" type="url" id="banner_url" name="banner_url" maxlength="2048" value="{{ old('banner_url', $game->banner_url ?? '') }}">
                    @error('banner_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="icon_media_id">{{ trans('gaming-hub-core::admin.fields.icon_media_id') }}</label>
                    <input class="form-control @error('icon_media_id') is-invalid @enderror" type="number" min="1" id="icon_media_id" name="icon_media_id" value="{{ old('icon_media_id', $game->icon_media_id ?? '') }}">
                    @error('icon_media_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="cover_media_id">{{ trans('gaming-hub-core::admin.fields.cover_media_id') }}</label>
                    <input class="form-control @error('cover_media_id') is-invalid @enderror" type="number" min="1" id="cover_media_id" name="cover_media_id" value="{{ old('cover_media_id', $game->cover_media_id ?? '') }}">
                    @error('cover_media_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" for="accent_color">{{ trans('gaming-hub-core::admin.fields.accent_color') }}</label>
                <input class="form-control form-control-color @error('accent_color') is-invalid @enderror" type="color" id="accent_color" name="accent_color" required value="{{ old('accent_color', $game->accent_color ?? '#6c5ce7') }}">
                @error('accent_color')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </section>
    </div>

    <div class="col-lg-4">
        <section class="card bg-light border-0" aria-labelledby="game-state-heading">
            <div class="card-body">
                <h3 class="h5" id="game-state-heading">{{ trans('gaming-hub-core::admin.games.sections.state') }}</h3>
                <p class="text-muted small">{{ trans('gaming-hub-core::admin.games.sections.state_help') }}</p>

                <div class="form-check mb-4">
                    <input type="hidden" name="enabled" value="0">
                    <input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" @checked(old('enabled', $game->enabled ?? true))>
                    <label class="form-check-label" for="enabled">{{ trans('gaming-hub-core::admin.fields.enabled') }}</label>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="sort_order">{{ trans('gaming-hub-core::admin.fields.sort_order') }}</label>
                    <input class="form-control @error('sort_order') is-invalid @enderror" type="number" id="sort_order" name="sort_order" required value="{{ old('sort_order', $game->sort_order ?? 0) }}">
                    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ trans('gaming-hub-core::admin.games.sections.order_help') }}</div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary" type="submit">{{ trans('gaming-hub-core::admin.actions.save') }}</button>
    <a class="btn btn-secondary" href="{{ route('gaming-hub-core.admin.games.index') }}">{{ trans('gaming-hub-core::admin.actions.cancel') }}</a>
</div>
