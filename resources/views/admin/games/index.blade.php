@extends('admin.layouts.admin')

@section('title', trans('gaming-hub-core::admin.games.title'))

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="h4 mb-1">{{ trans('gaming-hub-core::admin.games.title') }}</h2>
        <p class="text-muted mb-0">{{ trans('gaming-hub-core::admin.games.index_help') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <div class="btn-group" role="group" aria-label="{{ trans('gaming-hub-core::admin.games.view_mode') }}">
            <a class="btn btn-outline-secondary {{ $viewMode === 'grid' ? 'active' : '' }}" href="{{ route('gaming-hub-core.admin.games.index', ['view' => 'grid']) }}" @if($viewMode === 'grid') aria-current="page" @endif>
                <i class="bi bi-grid-3x3-gap me-1" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.grid') }}
            </a>
            <a class="btn btn-outline-secondary {{ $viewMode === 'list' ? 'active' : '' }}" href="{{ route('gaming-hub-core.admin.games.index', ['view' => 'list']) }}" @if($viewMode === 'list') aria-current="page" @endif>
                <i class="bi bi-list-ul me-1" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.list') }}
            </a>
        </div>
        @can('gaminghub.games.manage')
            <a class="btn btn-primary" href="{{ route('gaming-hub-core.admin.games.create') }}">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.create') }}
            </a>
        @endcan
    </div>
</div>

@if($games->total() === 0)
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="display-6 text-muted mb-3"><i class="bi bi-controller" aria-hidden="true"></i></div>
            <h3 class="h5">{{ trans('gaming-hub-core::admin.games.empty_title') }}</h3>
            <p class="text-muted mx-auto" style="max-width: 36rem;">{{ trans('gaming-hub-core::admin.games.empty_help') }}</p>
            @can('gaminghub.games.manage')
                <a class="btn btn-primary" href="{{ route('gaming-hub-core.admin.games.create') }}">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.create') }}
                </a>
            @endcan
        </div>
    </div>
@elseif($viewMode === 'list')
    <div class="card shadow-sm mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ trans('gaming-hub-core::admin.fields.game') }}</th>
                        <th>{{ trans('gaming-hub-core::admin.fields.status') }}</th>
                        <th>{{ trans('gaming-hub-core::admin.fields.servers') }}</th>
                        <th>{{ trans('gaming-hub-core::admin.fields.order') }}</th>
                        <th class="text-end">{{ trans('gaming-hub-core::admin.fields.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($games as $game)
                        @php($description = $game->shortDescriptionForDisplay())
                        <tr>
                            <td>
                                <div class="d-flex align-items-start gap-3">
                                    <div class="rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background-color: {{ $game->accent_color }}; color: {{ color_contrast($game->accent_color) }};">
                                        <span class="fw-semibold">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($game->short_name ?: $game->name, 0, 2)) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $game->name }}</div>
                                        @if($game->short_name && $game->short_name !== $game->name)
                                            <small class="text-muted">{{ $game->short_name }} · </small>
                                        @endif
                                        <small class="text-muted"><code>{{ $game->slug }}</code></small>
                                        @if($description)
                                            <div class="small text-muted mt-1">{{ \Illuminate\Support\Str::limit($description, 120) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $game->enabled ? 'success' : 'secondary' }}">
                                    {{ trans('gaming-hub-core::admin.states.'.($game->enabled ? 'enabled' : 'disabled')) }}
                                </span>
                            </td>
                            <td>
                                @can('gaminghub.servers.view')
                                    <a class="text-decoration-none" href="{{ route('gaming-hub-core.admin.games.servers.index', $game) }}">
                                        {{ trans_choice('gaming-hub-core::admin.games.server_count', $game->servers_count, ['count' => $game->servers_count]) }}
                                    </a>
                                @else
                                    {{ trans_choice('gaming-hub-core::admin.games.server_count', $game->servers_count, ['count' => $game->servers_count]) }}
                                @endcan
                            </td>
                            <td>{{ $game->sort_order }}</td>
                            <td class="text-end">@include('gaming-hub-core::admin.games._actions', ['game' => $game])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
        @foreach($games as $game)
            @php
                $artwork = $game->banner_url ?: $game->icon_url;
                $description = $game->shortDescriptionForDisplay();
                $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($game->short_name ?: $game->name, 0, 2));
            @endphp
            <div class="col">
                <div class="card h-100 shadow-sm overflow-hidden">
                    <div class="ratio ratio-16x9 position-relative" style="background-color: {{ $game->accent_color }}; color: {{ color_contrast($game->accent_color) }};">
                        @if($artwork)
                            <img src="{{ $artwork }}" alt="" class="w-100 h-100" style="object-fit: cover;" loading="lazy" onerror="this.classList.add('d-none'); this.nextElementSibling.classList.remove('d-none'); this.nextElementSibling.classList.add('d-flex');">
                            <div class="d-none position-absolute top-0 start-0 w-100 h-100 align-items-center justify-content-center">
                                <span class="display-5 fw-semibold">{{ $initials }}</span>
                            </div>
                        @else
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                <span class="display-5 fw-semibold">{{ $initials }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div>
                                <h3 class="h5 mb-1">{{ $game->name }}</h3>
                                <div class="small text-muted">
                                    @if($game->short_name && $game->short_name !== $game->name)
                                        <span>{{ $game->short_name }}</span><span aria-hidden="true"> · </span>
                                    @endif
                                    <code>{{ $game->slug }}</code>
                                </div>
                            </div>
                            <span class="badge bg-{{ $game->enabled ? 'success' : 'secondary' }} flex-shrink-0">
                                {{ trans('gaming-hub-core::admin.states.'.($game->enabled ? 'enabled' : 'disabled')) }}
                            </span>
                        </div>

                        @if($description)
                            <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($description, 180) }}</p>
                        @else
                            <p class="text-muted small fst-italic mb-3">{{ trans('gaming-hub-core::admin.games.no_description') }}</p>
                        @endif

                        <div class="mt-auto d-flex justify-content-between align-items-center gap-3 border-top pt-3">
                            <div class="small">
                                @can('gaminghub.servers.view')
                                    <a class="text-decoration-none" href="{{ route('gaming-hub-core.admin.games.servers.index', $game) }}">
                                        <i class="bi bi-hdd-stack me-1" aria-hidden="true"></i>{{ trans_choice('gaming-hub-core::admin.games.server_count', $game->servers_count, ['count' => $game->servers_count]) }}
                                    </a>
                                @else
                                    <span class="text-muted"><i class="bi bi-hdd-stack me-1" aria-hidden="true"></i>{{ trans_choice('gaming-hub-core::admin.games.server_count', $game->servers_count, ['count' => $game->servers_count]) }}</span>
                                @endcan
                            </div>
                            @include('gaming-hub-core::admin.games._actions', ['game' => $game])
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if($games->hasPages())
    {{ $games->links() }}
@endif
@endsection
