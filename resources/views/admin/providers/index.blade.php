@extends('admin.layouts.admin')

@section('title', trans('gaming-hub-core::admin.providers.for_server', ['server' => $server->name]))

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h4 mb-0">{{ trans('gaming-hub-core::admin.providers.for_server', ['server' => $server->name]) }}</h2>
                    <a href="{{ route('gaming-hub-core.admin.games.servers.index', $game) }}">
                        {{ trans('gaming-hub-core::admin.actions.back_servers') }}
                    </a>
                </div>

                @can('gaminghub.providers.manage')
                    @if (count($types) > 0)
                        <a class="btn btn-primary" href="{{ route('gaming-hub-core.admin.games.servers.providers.create', [$game, $server]) }}">
                            <i class="bi bi-plus-lg"></i>
                            {{ trans('gaming-hub-core::admin.actions.create_provider') }}
                        </a>
                    @endif
                @endcan
            </div>

            @if (count($types) === 0)
                <div class="alert alert-info">{{ trans('gaming-hub-core::admin.provider_messages.no_types') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>{{ trans('gaming-hub-core::admin.fields.order') }}</th>
                            <th>{{ trans('gaming-hub-core::admin.fields.name') }}</th>
                            <th>{{ trans('gaming-hub-core::admin.fields.provider_type') }}</th>
                            <th>{{ trans('gaming-hub-core::admin.fields.enabled') }}</th>
                            <th class="text-end">{{ trans('gaming-hub-core::admin.fields.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($providers as $provider)
                            <tr>
                                <td>{{ $provider->position }}</td>
                                <td>{{ $provider->name }}</td>
                                <td><code>{{ $provider->provider_type }}</code></td>
                                <td>
                                    <span class="badge bg-{{ $provider->enabled ? 'success' : 'secondary' }}">
                                        {{ trans_bool($provider->enabled) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @can('gaminghub.providers.manage')
                                        <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.servers.providers.move', [$game, $server, $provider, 'up']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-secondary" type="submit" @disabled($loop->first) aria-disabled="{{ $loop->first ? 'true' : 'false' }}">
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                        </form>

                                        <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.servers.providers.move', [$game, $server, $provider, 'down']) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-secondary" type="submit" @disabled($loop->last) aria-disabled="{{ $loop->last ? 'true' : 'false' }}">
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                        </form>

                                        <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.servers.providers.toggle', [$game, $server, $provider]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-sm btn-outline-warning" type="submit">
                                                <i class="bi bi-power"></i>
                                            </button>
                                        </form>

                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('gaming-hub-core.admin.games.servers.providers.edit', [$game, $server, $provider]) }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form class="d-inline" method="POST" action="{{ route('gaming-hub-core.admin.games.servers.providers.destroy', [$game, $server, $provider]) }}" onsubmit="return confirm('{{ trans('gaming-hub-core::admin.provider_messages.confirm_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    {{ trans('gaming-hub-core::admin.provider_messages.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
