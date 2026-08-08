@extends('admin.layouts.admin')
@section('title', trans('gaming-hub-core::admin.providers.create'))
@section('content')<div class="card shadow"><div class="card-body"><h2 class="h4">{{ trans('gaming-hub-core::admin.providers.create') }}</h2>@if(count($types)===0)<div class="alert alert-info">{{ trans('gaming-hub-core::admin.provider_messages.no_types') }}</div>@else<form method="POST" action="{{ route('gaming-hub-core.admin.games.servers.providers.store',[$game,$server]) }}">@include('gaming-hub-core::admin.providers._form')</form>@endif</div></div>@endsection
