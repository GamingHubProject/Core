@extends('admin.layouts.admin')
@section('title', trans('gaming-hub-core::admin.games.title'))
@section('content')
<div class="card shadow mb-4"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">{{ trans('gaming-hub-core::admin.games.title') }}</h2>
        @can('gaminghub.games.manage')<a class="btn btn-primary" href="{{ route('gaming-hub-core.admin.games.create') }}"><i class="bi bi-plus-lg"></i> {{ trans('gaming-hub-core::admin.actions.create') }}</a>@endcan
    </div>
    <div class="table-responsive"><table class="table table-striped align-middle">
        <thead><tr><th>{{ trans('gaming-hub-core::admin.fields.order') }}</th><th>{{ trans('gaming-hub-core::admin.fields.game') }}</th><th>{{ trans('gaming-hub-core::admin.fields.slug') }}</th><th>{{ trans('gaming-hub-core::admin.fields.enabled') }}</th><th class="text-end">{{ trans('gaming-hub-core::admin.fields.actions') }}</th></tr></thead>
        <tbody>@forelse($games as $game)<tr>
            <td>{{ $game->sort_order }}</td><td><x-game-badge :game="$game" /></td><td><code>{{ $game->slug }}</code><br><small class="text-muted">{{ $game->uuid }}</small></td>
            <td><span class="badge bg-{{ $game->enabled ? 'success' : 'secondary' }}">{{ trans_bool($game->enabled) }}</span></td>
            <td class="text-end">@can('gaminghub.games.manage')
                <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.move', [$game, 'up']) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" title="Move up"><i class="bi bi-arrow-up"></i></button></form>
                <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.move', [$game, 'down']) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary" title="Move down"><i class="bi bi-arrow-down"></i></button></form>
                <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.toggle', $game) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-warning"><i class="bi bi-power"></i></button></form>
                <a class="btn btn-sm btn-outline-info" href="{{ route('gaming-hub-core.admin.games.servers.index', $game) }}"><i class="bi bi-hdd-stack"></i> {{ trans('gaming-hub-core::admin.servers.title') }}</a>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('gaming-hub-core.admin.games.edit', $game) }}"><i class="bi bi-pencil"></i></a>
                <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.destroy', $game) }}" onsubmit="return confirm('{{ trans('gaming-hub-core::admin.messages.confirm_delete') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
            @endcan</td>
        </tr>@empty<tr><td colspan="5" class="text-center text-muted">{{ trans('gaming-hub-core::admin.messages.empty') }}</td></tr>@endforelse</tbody>
    </table></div>{{ $games->links() }}
</div></div>
@endsection
