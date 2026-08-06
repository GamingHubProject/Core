@extends('admin.layouts.admin')
@section('title', trans('gaming-hub-core::admin.games.edit'))
@section('content')<div class="card shadow"><div class="card-body"><h2 class="h4">{{ trans('gaming-hub-core::admin.games.edit') }}</h2><form method="POST" action="{{ route('gaming-hub-core.admin.games.update', $game) }}">@include('gaming-hub-core::admin.games._form')</form></div></div>@endsection
