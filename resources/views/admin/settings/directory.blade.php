@extends('admin.layouts.admin')

@section('title', trans('gaming-hub-core::admin.settings.title'))

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('gaming-hub-core.admin.settings.directory.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label" for="title">{{ trans('gaming-hub-core::admin.settings.page_title') }}</label>
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" maxlength="120" required value="{{ old('title', $settings['title']) }}">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label" for="description">{{ trans('gaming-hub-core::admin.settings.description') }}</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" maxlength="2000">{{ old('description', $settings['description']) }}</textarea>
                <div class="form-text">{{ trans('gaming-hub-core::admin.settings.description_help') }}</div>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="columns">{{ trans('gaming-hub-core::admin.settings.columns') }}</label>
                    <select class="form-select" id="columns" name="columns">
                        @foreach([1,2,3,4] as $value)<option value="{{ $value }}" @selected((int) old('columns', $settings['columns']) === $value)>{{ $value }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="density">{{ trans('gaming-hub-core::admin.settings.density') }}</label>
                    <select class="form-select" id="density" name="density">
                        @foreach(['compact','standard'] as $value)<option value="{{ $value }}" @selected(old('density', $settings['density']) === $value)>{{ trans('gaming-hub-core::admin.settings.values.'.$value) }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="container_width">{{ trans('gaming-hub-core::admin.settings.width') }}</label>
                    <select class="form-select" id="container_width" name="container_width">
                        @foreach(['normal','wide','full'] as $value)<option value="{{ $value }}" @selected(old('container_width', $settings['container_width']) === $value)>{{ trans('gaming-hub-core::admin.settings.values.'.$value) }}</option>@endforeach
                    </select>
                </div>
            </div>

            <hr>
            <div class="row g-2">
                @foreach(['show_description','show_status','show_provider_message','show_button','show_count'] as $field)
                    <div class="col-md-6">
                        <div class="form-check form-switch">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" @checked((bool) old($field, $settings[$field]))>
                            <label class="form-check-label" for="{{ $field }}">{{ trans('gaming-hub-core::admin.settings.'.$field) }}</label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3">
                <label class="form-label" for="fallback_style">{{ trans('gaming-hub-core::admin.settings.fallback') }}</label>
                <select class="form-select" id="fallback_style" name="fallback_style">
                    @foreach(['compact','media'] as $value)<option value="{{ $value }}" @selected(old('fallback_style', $settings['fallback_style']) === $value)>{{ trans('gaming-hub-core::admin.settings.values.fallback_'.$value) }}</option>@endforeach
                </select>
            </div>

            <div class="form-check form-switch mt-3">
                <input type="hidden" name="show_servers_on_game_page" value="0">
                <input class="form-check-input" type="checkbox" id="show_servers_on_game_page" name="show_servers_on_game_page" value="1" @checked((bool) old('show_servers_on_game_page', $settings['show_servers_on_game_page']))>
                <label class="form-check-label" for="show_servers_on_game_page">{{ trans('gaming-hub-core::admin.settings.show_servers') }}</label>
            </div>

            <hr class="my-4">
            <h2 class="h4">{{ trans('gaming-hub-core::admin.settings.game_page_title') }}</h2>
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="server_density">{{ trans('gaming-hub-core::admin.settings.server_density') }}</label><select class="form-select" id="server_density" name="server_density">@foreach(['compact','standard'] as $value)<option value="{{ $value }}" @selected(old('server_density',$gamePage['server_density'])===$value)>{{ trans('gaming-hub-core::admin.settings.values.'.$value) }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label" for="server_columns">{{ trans('gaming-hub-core::admin.settings.server_columns') }}</label><select class="form-select" id="server_columns" name="server_columns">@foreach([1,2,3] as $value)<option value="{{ $value }}" @selected((int)old('server_columns',$gamePage['server_columns'])===$value)>{{ $value }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label" for="show_address">{{ trans('gaming-hub-core::admin.settings.show_address') }}</label><select class="form-select" id="show_address" name="show_address">@foreach(['hidden','hostname','hostname_and_port'] as $value)<option value="{{ $value }}" @selected(old('show_address',$gamePage['show_address'])===$value)>{{ trans('gaming-hub-core::admin.settings.values.address_'.$value) }}</option>@endforeach</select></div>
            </div>
            <div class="row g-2 mt-2">
                @foreach(['show_servers','show_server_descriptions','game_page_show_status','game_page_show_provider_message','show_join_button','show_game_navigation'] as $field)
                    @php($key = match($field){'game_page_show_status'=>'show_status','game_page_show_provider_message'=>'show_provider_message','show_game_navigation'=>'show_navigation',default=>$field})
                    <div class="col-md-6"><div class="form-check form-switch"><input type="hidden" name="{{ $field }}" value="0"><input class="form-check-input" type="checkbox" id="{{ $field }}" name="{{ $field }}" value="1" @checked((bool)old($field,$gamePage[$key]))><label class="form-check-label" for="{{ $field }}">{{ trans('gaming-hub-core::admin.settings.'.$field) }}</label></div></div>
                @endforeach
            </div>

            <button class="btn btn-primary mt-4" type="submit">{{ trans('messages.actions.save') }}</button>
        </form>
    </div>
</div>
@endsection
