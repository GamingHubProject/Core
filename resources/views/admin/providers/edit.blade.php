@extends('admin.layouts.admin')
@section('title', trans('gaming-hub-core::admin.providers.edit'))
@section('content')<div class="card shadow"><div class="card-body"><h2 class="h4">{{ trans('gaming-hub-core::admin.providers.edit') }}</h2><form method="POST" action="{{ route('gaming-hub-core.admin.games.servers.providers.update',[$game,$server,$provider]) }}">@include('gaming-hub-core::admin.providers._form')</form></div></div>@endsection
