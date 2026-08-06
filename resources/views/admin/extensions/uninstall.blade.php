@extends('admin.layouts.admin')

@section('title', 'Uninstall '.$extension->extension_id)

@section('content')
<div class="container">
    <a href="{{ route('gaming-hub-core.admin.extensions.index', ['tab' => 'installed']) }}">← Extensions</a>

    <div class="card border-danger mt-3">
        <div class="card-header bg-danger text-white">
            Confirm extension uninstall
        </div>
        <div class="card-body">
            <h1 class="h4">{{ $extension->manifest_snapshot['name'] ?? $extension->extension_id }}</h1>

            <dl class="row">
                <dt class="col-sm-4">Extension ID</dt>
                <dd class="col-sm-8"><code>{{ $extension->extension_id }}</code></dd>

                <dt class="col-sm-4">Installed version</dt>
                <dd class="col-sm-8">{{ $extension->installed_version }}</dd>

                <dt class="col-sm-4">Source</dt>
                <dd class="col-sm-8">{{ $extension->source_id ?: 'Local filesystem' }}</dd>

                <dt class="col-sm-4">Enabled</dt>
                <dd class="col-sm-8">{{ $enabled ? 'Yes — it will be disabled first' : 'No' }}</dd>

                <dt class="col-sm-4">Extension data</dt>
                <dd class="col-sm-8"><strong>Retained.</strong> Database tables and user data are not purged.</dd>
            </dl>

            @if ($dependents !== [])
                <div class="alert alert-danger">
                    <strong>Uninstall is blocked.</strong>
                    Installed dependents: {{ implode(', ', array_column($dependents, 'id')) }}.
                </div>
            @else
                <div class="alert alert-warning">
                    This removes only the validated plugin directory and installed-extension metadata. A future purge-data action is intentionally not included.
                </div>

                <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.installed.uninstall', $extension) }}">
                    @csrf
                    @method('DELETE')

                    <div class="mb-3">
                        <label class="form-label" for="confirmation">
                            Type <code>{{ $extension->extension_id }}</code> to confirm
                        </label>
                        <input class="form-control" id="confirmation" name="confirmation" autocomplete="off" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="retain_data" value="1" id="retain-data" required>
                        <label class="form-check-label" for="retain-data">
                            I understand extension database data will be retained.
                        </label>
                    </div>

                    <button class="btn btn-danger">Remove extension files</button>
                    <a class="btn btn-secondary" href="{{ route('gaming-hub-core.admin.extensions.index', ['tab' => 'installed']) }}">Cancel</a>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
