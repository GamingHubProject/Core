@extends('admin.layouts.admin')

@section('title', $extension->extension_id)

@section('content')
<div class="container">
    <a href="{{ route('gaming-hub-core.admin.extensions.index', ['tab' => 'installed']) }}">← Extensions</a>

    <div class="card mt-3">
        <div class="card-body">
            <h1>{{ $extension->manifest_snapshot['name'] ?? $extension->extension_id }}</h1>
            <p>{{ $extension->manifest_snapshot['description'] ?? '' }}</p>

            <dl class="row">
                <dt class="col-sm-3">Installed version</dt>
                <dd class="col-sm-9">{{ $extension->installed_version }}</dd>

                <dt class="col-sm-3">Source</dt>
                <dd class="col-sm-9">{{ $extension->source_id ?: 'Local filesystem' }}</dd>

                <dt class="col-sm-3">State</dt>
                <dd class="col-sm-9">{{ $enabled ? 'Enabled' : 'Disabled' }}</dd>

                <dt class="col-sm-3">Trust</dt>
                <dd class="col-sm-9">{{ $extension->trust_level }}</dd>

                <dt class="col-sm-3">Checksum</dt>
                <dd class="col-sm-9">{{ $extension->checksum_verified ? 'SHA-256 verified' : 'Not verified' }}</dd>

                <dt class="col-sm-3">Provides</dt>
                <dd class="col-sm-9"><pre>{{ json_encode($extension->manifest_snapshot['provides'] ?? [], JSON_PRETTY_PRINT) }}</pre></dd>

                <dt class="col-sm-3">Consumes</dt>
                <dd class="col-sm-9"><pre>{{ json_encode($extension->manifest_snapshot['consumes'] ?? [], JSON_PRETTY_PRINT) }}</pre></dd>

                <dt class="col-sm-3">Dependencies</dt>
                <dd class="col-sm-9"><pre>{{ json_encode($extension->manifest_snapshot['requires'] ?? [], JSON_PRETTY_PRINT) }}</pre></dd>
            </dl>

            @if ($dependents !== [])
                <div class="alert alert-warning">
                    Installed dependents: {{ implode(', ', array_column($dependents, 'id')) }}.
                </div>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                @if ($enabled)
                    <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.installed.disable', $extension) }}">
                        @csrf
                        @method('PATCH')
                        @if ($dependents !== [])
                            <label class="me-2">
                                <input type="checkbox" name="confirm_dependents" value="1">
                                Confirm dependent warning
                            </label>
                        @endif
                        <button class="btn btn-warning">Disable</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.installed.enable', $extension) }}">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success">Enable</button>
                    </form>
                @endif

                <a class="btn btn-outline-danger" href="{{ route('gaming-hub-core.admin.extensions.installed.uninstall.confirm', $extension) }}">
                    Uninstall
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
