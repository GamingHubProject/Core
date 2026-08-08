<div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
    @can('gaminghub.games.manage')
        <div class="btn-group btn-group-sm" role="group" aria-label="{{ trans('gaming-hub-core::admin.fields.order') }}">
            <form method="POST" action="{{ route('gaming-hub-core.admin.games.move', [$game, 'up']) }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-outline-secondary rounded-end-0" type="submit" title="{{ trans('gaming-hub-core::admin.actions.move_up') }}" aria-label="{{ trans('gaming-hub-core::admin.actions.move_up') }}">
                    <i class="bi bi-arrow-up" aria-hidden="true"></i>
                </button>
            </form>
            <form method="POST" action="{{ route('gaming-hub-core.admin.games.move', [$game, 'down']) }}">
                @csrf
                @method('PATCH')
                <button class="btn btn-outline-secondary rounded-start-0" type="submit" title="{{ trans('gaming-hub-core::admin.actions.move_down') }}" aria-label="{{ trans('gaming-hub-core::admin.actions.move_down') }}">
                    <i class="bi bi-arrow-down" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    @endcan

    @canany(['gaminghub.servers.view', 'gaminghub.games.manage'])
    <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            {{ trans('gaming-hub-core::admin.fields.actions') }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            @can('gaminghub.servers.view')
                <li>
                    <a class="dropdown-item" href="{{ route('gaming-hub-core.admin.games.servers.index', $game) }}">
                        <i class="bi bi-hdd-stack me-2" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.servers') }}
                    </a>
                </li>
            @endcan
            @can('gaminghub.games.manage')
                <li>
                    <a class="dropdown-item" href="{{ route('gaming-hub-core.admin.games.edit', $game) }}">
                        <i class="bi bi-pencil me-2" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.edit') }}
                    </a>
                </li>
                <li>
                    <form method="POST" action="{{ route('gaming-hub-core.admin.games.toggle', $game) }}">
                        @csrf
                        @method('PATCH')
                        <button class="dropdown-item" type="submit">
                            <i class="bi bi-power me-2" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.'.($game->enabled ? 'disable' : 'enable')) }}
                        </button>
                    </form>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('gaming-hub-core.admin.games.destroy', $game) }}" onsubmit="return confirm('{{ trans('gaming-hub-core::admin.messages.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="bi bi-trash me-2" aria-hidden="true"></i>{{ trans('gaming-hub-core::admin.actions.delete') }}
                        </button>
                    </form>
                </li>
            @endcan
        </ul>
    </div>
    @endcanany
</div>
