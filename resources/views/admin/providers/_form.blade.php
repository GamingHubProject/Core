@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
@csrf
@if(isset($provider)) @method('PUT') @endif
@php($selectedType = old('provider_type', $provider->provider_type ?? ($types[0]->id ?? null)))
<div class="mb-3"><label class="form-label" for="provider_type">{{ trans('gaming-hub-core::admin.fields.provider_type') }}</label><select class="form-select @error('provider_type') is-invalid @enderror" id="provider_type" name="provider_type" required>@foreach($types as $type)<option value="{{ $type->id }}" @selected($selectedType===$type->id)>{{ $type->name }} ({{ $type->id }})</option>@endforeach</select>@error('provider_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label" for="name">{{ trans('gaming-hub-core::admin.fields.name') }}</label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" maxlength="255" required value="{{ old('name',$provider->name ?? '') }}">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label" for="position">{{ trans('gaming-hub-core::admin.fields.order') }}</label><input class="form-control @error('position') is-invalid @enderror" type="number" min="1" id="position" name="position" value="{{ old('position', $provider->position ?? $nextPosition ?? 1) }}">@error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-check mb-3"><input type="hidden" name="enabled" value="0"><input class="form-check-input" type="checkbox" id="enabled" name="enabled" value="1" @checked(old('enabled',$provider->enabled ?? true))><label class="form-check-label" for="enabled">{{ trans('gaming-hub-core::admin.fields.enabled') }}</label></div>
@foreach($types as $type)
<fieldset class="provider-config border rounded p-3 mb-3" data-provider-type="{{ $type->id }}">
    <legend class="h6">{{ $type->name }}</legend>
    <p class="text-muted">{{ $type->description }}</p>
    @foreach($type->fields as $field)
        @php($value=old('configuration.'.$field->key,($provider->provider_type ?? null)===$type->id ? data_get($provider->configuration,$field->key) : null))
        <div class="mb-3">
            <label class="form-label" for="config_{{ $type->id }}_{{ $field->key }}">{{ $field->label }}</label>
            @if($field->type==='boolean')
                <input type="hidden" name="configuration[{{ $field->key }}]" value="0">
                <input class="form-check-input" id="config_{{ $type->id }}_{{ $field->key }}" type="checkbox" name="configuration[{{ $field->key }}]" value="1" @checked($value)>
            @elseif($field->type==='select')
                <select class="form-select" id="config_{{ $type->id }}_{{ $field->key }}" name="configuration[{{ $field->key }}]" @required($field->required)>@foreach($field->options as $option)<option value="{{ $option }}" @selected($value===$option)>{{ ucfirst($option) }}</option>@endforeach</select>
            @else
                <input class="form-control" id="config_{{ $type->id }}_{{ $field->key }}" type="{{ $field->secret?'password':($field->type==='integer'?'number':'text') }}" name="configuration[{{ $field->key }}]" value="{{ $field->secret?'':$value }}" @if($field->maxLength) maxlength="{{ $field->maxLength }}" @endif @required($field->required)>
            @endif
            @if($field->help)<div class="form-text">{{ $field->help }}</div>@endif
            @error('configuration.'.$field->key)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    @endforeach
</fieldset>
@endforeach
<button class="btn btn-primary" type="submit">{{ trans('gaming-hub-core::admin.actions.save') }}</button><a class="btn btn-secondary" href="{{ route('gaming-hub-core.admin.games.servers.providers.index',[$game,$server]) }}">{{ trans('gaming-hub-core::admin.actions.cancel') }}</a>
@push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{const select=document.getElementById('provider_type');if(!select)return;const sync=()=>document.querySelectorAll('.provider-config').forEach(el=>{const active=el.dataset.providerType===select.value;el.hidden=!active;el.querySelectorAll('input,select,textarea').forEach(input=>input.disabled=!active);});select.addEventListener('change',sync);sync();});</script>@endpush
