@extends('admin.layouts.admin')

@section('title', 'Gaming Hub Extensions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Gaming Hub Extensions</h1>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Extension operation failed.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="alert alert-danger">
        <strong>Security warning:</strong>
        Extensions execute PHP with access to this Azuriom installation. Only use sources you trust.
    </div>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link {{ request('tab', 'installed') === 'installed' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#installed">Installed</button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request('tab') === 'available' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#available">Available</button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request('tab') === 'registries' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#registries">Registries</button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ request('tab') === 'logs' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#logs">Operation Logs</button>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade {{ request('tab', 'installed') === 'installed' ? 'show active' : '' }}" id="installed">
            <div class="card">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Extension</th>
                                <th>Version</th>
                                <th>Source</th>
                                <th>Trust</th>
                                <th>Checksum</th>
                                <th>State</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($installed as $extension)
                                @php
                                    $update = $updates[$extension->extension_id] ?? null;
                                    $installedState = $installedStates[$extension->extension_id] ?? 'installed';
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('gaming-hub-core.admin.extensions.installed.show', $extension) }}">
                                            {{ $extension->manifest_snapshot['name'] ?? $extension->extension_id }}
                                        </a>
                                        <div class="small text-muted">{{ $extension->extension_id }}</div>
                                    </td>
                                    <td>{{ $extension->installed_version }}</td>
                                    <td>{{ $extension->source_id ?: 'Local' }}</td>
                                    <td>{{ ucfirst($extension->trust_level) }}</td>
                                    <td>{{ $extension->checksum_verified ? 'Verified' : 'Not verified' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $extension->enabled_snapshot ? 'success' : 'secondary' }}">
                                            {{ $extension->enabled_snapshot ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            @if ($update !== null)
                                                <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.update', $update['source']) }}">
                                                    @csrf
                                                    <input type="hidden" name="extension_id" value="{{ $extension->extension_id }}">
                                                    @if (! $update['source']->trusted && $update['source']->type !== 'official')
                                                        <label class="small d-block mb-1">
                                                            <input type="checkbox" name="confirm_unverified" value="1" required>
                                                            Confirm untrusted source
                                                        </label>
                                                    @endif
                                                    <button class="btn btn-sm btn-primary">
                                                        Update to {{ $update['latest_version'] }}
                                                    </button>
                                                </form>
                                            @elseif ($installedState === 'up_to_date')
                                                <span class="badge bg-success align-self-center">Up to date</span>
                                            @elseif ($installedState === 'incompatible')
                                                <span class="badge bg-danger align-self-center">Incompatible update</span>
                                            @else
                                                <span class="badge bg-secondary align-self-center">Installed</span>
                                            @endif

                                            <a class="btn btn-sm btn-outline-danger" href="{{ route('gaming-hub-core.admin.extensions.installed.uninstall.confirm', $extension) }}">
                                                Uninstall
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted">No Gaming Hub extensions installed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade {{ request('tab') === 'available' ? 'show active' : '' }}" id="available">
            @foreach ($sources->where('enabled', true) as $source)
                @php
                    $sourceData = $catalog[$source->id] ?? [];
                @endphp

                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <strong>{{ $source->name }}</strong>
                        <span class="badge bg-{{ $source->trust_level === 'official' ? 'primary' : ($source->trusted ? 'success' : 'warning') }}">
                            {{ ucfirst($source->trust_level) }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if (isset($sourceData['registry']))
                            <div class="row g-3">
                                @foreach ($sourceData['registry']->extensions as $extension)
                                    @php
                                        $stateData = $catalogStates[$source->id][$extension->id] ?? ['state' => 'available', 'installed' => null];
                                        $state = $stateData['state'];
                                    @endphp
                                    <div class="col-xl-4 col-md-6">
                                        <div class="card h-100">
                                            <div class="card-body d-flex flex-column">
                                                <h3 class="h5">{{ $extension->name }}</h3>
                                                <p>{{ $extension->description }}</p>
                                                <small class="text-muted">{{ $extension->author }} · {{ $extension->latestVersion }}</small>

                                                <div class="mt-auto pt-3">
                                                    @if ($state === 'incompatible')
                                                        <span class="badge bg-danger">Incompatible</span>
                                                    @elseif ($state === 'update')
                                                        <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.update', $source) }}">
                                                            @csrf
                                                            <input type="hidden" name="extension_id" value="{{ $extension->id }}">
                                                            @if (! $source->trusted && $source->type !== 'official')
                                                                <label class="d-block mb-2">
                                                                    <input type="checkbox" name="confirm_unverified" value="1" required>
                                                                    I understand this source is untrusted.
                                                                </label>
                                                            @endif
                                                            <button class="btn btn-primary">Update available</button>
                                                        </form>
                                                    @elseif ($state === 'up_to_date')
                                                        <span class="badge bg-success">Up to date</span>
                                                    @elseif ($state === 'installed')
                                                        <span class="badge bg-secondary">Installed</span>
                                                        <div class="small text-muted mt-1">The source version is not newer; downgrade is blocked.</div>
                                                    @else
                                                        <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.install', $source) }}">
                                                            @csrf
                                                            <input type="hidden" name="extension_id" value="{{ $extension->id }}">
                                                            @if (! $source->trusted && $source->type !== 'official')
                                                                <label class="d-block mb-2">
                                                                    <input type="checkbox" name="confirm_unverified" value="1" required>
                                                                    I understand this source is untrusted.
                                                                </label>
                                                            @endif
                                                            <button class="btn btn-primary">Install</button>
                                                            <label class="ms-2">
                                                                <input type="checkbox" name="enable" value="1">
                                                                Enable now
                                                            </label>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @elseif (isset($sourceData['release']))
                            @php
                                $directState = $catalogStates[$source->id]['direct'] ?? ['state' => 'available', 'installed' => null];
                            @endphp
                            <p>
                                Latest stable GitHub release:
                                <strong>{{ $sourceData['release']['tag_name'] ?? 'Unknown' }}</strong>.
                                Only packaged ZIP assets are eligible.
                            </p>

                            @if ($directState['state'] === 'update' && $directState['installed'] !== null)
                                <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.update', $source) }}">
                                    @csrf
                                    <input type="hidden" name="extension_id" value="{{ $directState['installed']->extension_id }}">
                                    <label>
                                        <input type="checkbox" name="confirm_unverified" value="1" required>
                                        I understand this direct repository is unverified.
                                    </label>
                                    <button class="btn btn-primary ms-2">Update available</button>
                                </form>
                            @elseif ($directState['state'] === 'up_to_date')
                                <span class="badge bg-success">Up to date</span>
                            @elseif ($directState['state'] === 'installed')
                                <span class="badge bg-secondary">Installed</span>
                            @else
                                <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.install', $source) }}">
                                    @csrf
                                    <input type="hidden" name="extension_id" value="direct">
                                    <label>
                                        <input type="checkbox" name="confirm_unverified" value="1" required>
                                        I understand this direct repository is unverified.
                                    </label>
                                    <button class="btn btn-primary ms-2">Inspect and install packaged release</button>
                                </form>
                            @endif
                        @else
                            <p class="text-danger">{{ $sourceData['error'] ?? 'Source unavailable.' }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="tab-pane fade {{ request('tab') === 'registries' ? 'show active' : '' }}" id="registries">
            <div class="card mb-3">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Trust</th>
                                <th>Enabled</th>
                                <th>Last refresh</th>
                                <th>Error</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sources as $source)
                                <tr>
                                    <td>{{ $source->name }}</td>
                                    <td>{{ $source->type }}</td>
                                    <td>{{ $source->trust_level }}</td>
                                    <td>{{ $source->enabled ? 'Yes' : 'No' }}</td>
                                    <td>{{ $source->last_successful_refresh_at }}</td>
                                    <td>{{ $source->last_error }}</td>
                                    <td>
                                        <div class="d-flex gap-1 flex-wrap">
                                            <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.sources.refresh', $source) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-primary">Refresh</button>
                                            </form>

                                            @if ($source->type !== 'official')
                                                <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.sources.toggle', $source) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-outline-secondary">Toggle</button>
                                                </form>
                                                <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.sources.trust', $source) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-sm btn-outline-warning">Trust / untrust</button>
                                                </form>
                                                <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.sources.destroy', $source) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="h5">Add source</h2>
                    <form method="POST" action="{{ route('gaming-hub-core.admin.extensions.sources.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-2">
                                <select class="form-select" name="type">
                                    <option value="registry">Custom registry</option>
                                    <option value="github">Direct GitHub repository</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" name="name" placeholder="Name" required>
                            </div>
                            <div class="col-md-7">
                                <input class="form-control" name="url" type="url" placeholder="https://..." required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label>
                                <input type="checkbox" name="acknowledge" value="1" required>
                                I understand this source can install executable PHP code.
                            </label>
                        </div>
                        <div>
                            <label><input type="checkbox" name="trusted" value="1"> Mark trusted</label>
                            <label class="ms-3"><input type="checkbox" name="enabled" value="1"> Enable immediately</label>
                            <label class="ms-3"><input type="checkbox" name="allow_prereleases" value="1"> Allow prereleases</label>
                        </div>
                        <button class="btn btn-primary mt-3">Add source</button>
                    </form>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                Writable paths:
                <code>{{ $pluginPath }}</code>,
                <code>{{ $stagingPath }}</code>,
                <code>{{ $backupPath }}</code>.
                Core never changes them to world-writable automatically.
            </div>
        </div>

        <div class="tab-pane fade {{ request('tab') === 'logs' ? 'show active' : '' }}" id="logs">
            <div class="card">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Operation</th>
                                <th>Extension</th>
                                <th>Stage</th>
                                <th>Result</th>
                                <th>Summary and events</th>
                                <th>Rollback</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($operations as $operation)
                                @php
                                    $stage = $operation->current_stage ?: 'resolving';
                                    $stageClass = in_array($stage, ['completed'], true)
                                        ? 'success'
                                        : (in_array($stage, ['failed', 'rollback_failed'], true)
                                            ? 'danger'
                                            : ($stage === 'rolled_back' ? 'warning' : 'primary'));
                                    $stageMessage = [
                                        'resolving' => 'Resolving…',
                                        'downloading' => 'Downloading…',
                                        'validating' => 'Validating…',
                                        'staging' => 'Staging…',
                                        'backing_up' => 'Creating backup…',
                                        'disabling' => 'Disabling…',
                                        'replacing' => 'Replacing…',
                                        'migrating' => 'Running migrations…',
                                        'enabling' => 'Restoring state…',
                                        'removing' => 'Removing files…',
                                        'cleaning' => 'Cleaning caches…',
                                        'rolling_back' => 'Rolling back…',
                                    ][$stage] ?? 'Processing…';
                                @endphp
                                <tr>
                                    <td class="text-nowrap">{{ $operation->started_at }}</td>
                                    <td>{{ ucfirst($operation->operation) }}</td>
                                    <td>
                                        {{ $operation->extension_id ?: 'Resolving…' }}
                                        @if ($operation->version)
                                            <div class="small text-muted">{{ $operation->version }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $stageClass }}">{{ ucfirst(str_replace('_', ' ', $stage)) }}</span>
                                        @if ($operation->result === 'running')
                                            <div class="small text-muted mt-1">{{ $stageMessage }}</div>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($operation->result) }}</td>
                                    <td style="min-width: 320px">
                                        @if ($operation->summary)
                                            <div>{{ $operation->summary }}</div>
                                        @endif

                                        @if (! empty($operation->events))
                                            <details class="mt-1">
                                                <summary>{{ count($operation->events) }} lifecycle events</summary>
                                                <ol class="small mt-2 mb-0">
                                                    @foreach ($operation->events as $event)
                                                        <li>
                                                            <strong>{{ ucfirst(str_replace('_', ' ', $event['stage'] ?? 'event')) }}:</strong>
                                                            {{ $event['message'] ?? '' }}
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            </details>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($operation->rollback_attempted)
                                            {{ $operation->rollback_succeeded ? 'Succeeded' : 'Failed' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-muted">No extension operations recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
