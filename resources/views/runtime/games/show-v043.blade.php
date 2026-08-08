@extends('layouts.app')
@section('title', $game->name)
@section('content')
<style>
.gh-game-page{width:min(calc(100% - 2rem),1440px);margin-inline:auto;padding-block:.25rem 2rem}.gh-game-hero{position:relative;display:flex;align-items:flex-end;min-height:clamp(240px,32vw,430px);overflow:hidden;border:1px solid rgba(255,255,255,.08);border-radius:var(--bs-border-radius-lg,1rem);margin-bottom:1.5rem;background:radial-gradient(circle at 15% 15%,rgba(90,120,255,.25),transparent 45%),linear-gradient(135deg,#172033,#0b101a 70%)}.gh-game-hero--image{background-position:center;background-size:cover}.gh-game-hero::after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(5,8,14,.08) 15%,rgba(5,8,14,.88) 100%)}.gh-game-hero__identity{position:relative;z-index:1;display:flex;gap:1rem;align-items:center;width:100%;padding:clamp(1.25rem,3vw,2.5rem);color:#fff}.gh-game-hero__copy{min-width:0}.gh-game-hero__copy h1{color:inherit;text-shadow:0 2px 18px rgba(0,0,0,.55)}.gh-game-hero__copy p{max-width:760px;color:rgba(255,255,255,.82)}.gh-game-icon,.gh-server-icon{flex:0 0 auto;display:grid;place-items:center;overflow:hidden;border:1px solid rgba(255,255,255,.12);background:var(--bs-secondary-bg,#202938);box-shadow:0 10px 30px rgba(0,0,0,.25)}.gh-game-icon{width:88px;height:88px;border-radius:1rem}.gh-server-icon{width:56px;height:56px;border-radius:.75rem}.gh-game-icon img,.gh-server-icon img{width:100%;height:100%;object-fit:cover}.gh-game-layout{display:grid;grid-template-columns:minmax(0,1fr) 270px;gap:1.5rem}.gh-game-section{margin-bottom:1.5rem}.gh-game-section__card{height:auto}.gh-server-grid{display:grid;gap:1rem;align-items:stretch}.gh-server-grid--1{grid-template-columns:1fr}.gh-server-grid--2{grid-template-columns:repeat(2,minmax(0,1fr))}.gh-server-grid--3{grid-template-columns:repeat(3,minmax(0,1fr))}.gh-server-card{height:100%;display:flex;flex-direction:column;overflow:hidden}.gh-server-card .card-body{display:flex;flex-direction:column;flex:1}.gh-server-card--compact{min-height:225px}.gh-server-card--compact .card-body{padding:1rem}.gh-server-card--standard{min-height:285px}.gh-server-card--standard .card-body{padding:1.5rem}.gh-server-card__header{display:flex;gap:.85rem;align-items:flex-start}.gh-server-card__copy{min-width:0}.gh-server-card__description{display:-webkit-box;overflow:hidden;-webkit-box-orient:vertical;-webkit-line-clamp:3}.gh-server-actions{margin-top:auto;display:flex;flex-wrap:wrap;gap:.5rem;padding-top:.75rem}.gh-game-navigation{position:sticky;top:1rem}.gh-game-navigation__items{display:flex;flex-direction:column;gap:.25rem}.gh-game-navigation a{display:flex;gap:.6rem;align-items:center;padding:.65rem .75rem;border-radius:.5rem;text-decoration:none}.gh-game-navigation a.active,.gh-game-navigation a[aria-current="page"]{background:var(--bs-primary-bg-subtle,rgba(13,110,253,.14));font-weight:600}.gh-empty-state{padding:2rem;text-align:center}.gh-address{font-family:var(--bs-font-monospace,monospace);overflow-wrap:anywhere}.gh-runtime-marker{display:none!important}@media(max-width:1199.98px){.gh-server-grid--3{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:991.98px){.gh-game-layout{grid-template-columns:1fr}.gh-game-navigation{position:static}.gh-game-navigation__items{flex-direction:row;flex-wrap:wrap}}@media(max-width:767.98px){.gh-game-page{width:min(calc(100% - 1rem),1440px)}.gh-server-grid{grid-template-columns:1fr}.gh-game-hero{min-height:230px}.gh-game-hero__identity{align-items:flex-start}.gh-game-icon{width:68px;height:68px}}@media(max-width:420px){.gh-game-hero__identity{flex-direction:column}.gh-game-navigation__items{display:grid;grid-template-columns:1fr 1fr}}
</style>
<div class="gh-game-page" data-gh-core-game-view="{{ $gamingHubCoreViewVersion }}">
    <span class="gh-runtime-marker" aria-hidden="true">gaming-hub-core-game-view-0.4.3</span>
    <header id="overview" @class(['gh-game-hero','gh-game-hero--image' => filled($game->bannerUrl)]) @if(filled($game->bannerUrl)) style="background-image:linear-gradient(180deg,rgba(5,8,14,.08) 15%,rgba(5,8,14,.88) 100%),url('{{ $game->bannerUrl }}')" @endif>
        <div class="gh-game-hero__identity">
            <div class="gh-game-icon">
                @if(filled($game->iconUrl))<img src="{{ $game->iconUrl }}" alt="">@else<span class="fs-2" aria-hidden="true">🎮</span>@endif
            </div>
            <div class="gh-game-hero__copy">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <h1 class="mb-0">{{ $game->name }}</h1>
                    @if($settings['show_status'] && filled($game->status))<span class="badge bg-secondary gh-status-badge gh-status--{{ $game->status }}">{{ trans('gaming-hub-core::public.statuses.'.$game->status) }}</span>@endif
                </div>
                @if(filled($game->shortDescription))<p class="mb-0 mt-2">{{ $game->shortDescription }}</p>@endif
            </div>
        </div>
    </header>

    <div class="gh-game-layout">
        <main>
            @if(filled($game->shortDescription))
                <section class="card gh-game-section gh-game-section__card"><div class="card-body"><h2 class="h4">{{ trans('gaming-hub-core::public.overview') }}</h2><p class="mb-0">{{ $game->shortDescription }}</p></div></section>
            @endif
            @if(filled($game->longDescription))
                <section class="card gh-game-section gh-game-section__card"><div class="card-body"><h2 class="h4">{{ trans('gaming-hub-core::public.description') }}</h2><div>{!! nl2br(e($game->longDescription)) !!}</div></div></section>
            @endif

            @if($settings['show_servers'])
                <section id="servers" class="gh-game-section" data-gh-public-server-count="{{ $publicGameServers->count() }}">
                    <h2 class="h3 mb-3">{{ trans('gaming-hub-core::public.servers') }}</h2>
                    <div class="gh-server-grid {{ $settings['grid_class'] }}" data-gh-server-columns="{{ $settings['server_columns'] }}">
                    @forelse($publicGameServers as $server)
                        @php
                            $address = null;
                            if ($settings['show_address'] !== 'hidden' && filled($server->hostname)) {
                                $address = $server->hostname;
                                if ($settings['show_address'] === 'hostname_and_port' && $server->displayPort) {
                                    $address .= ':'.$server->displayPort;
                                }
                            }
                            $joinUrl = filled($server->joinUrl) && filter_var($server->joinUrl, FILTER_VALIDATE_URL) ? $server->joinUrl : null;
                        @endphp
                        <article class="card gh-server-card {{ $settings['card_class'] }}" data-gh-server-id="{{ $server->id }}" data-gh-server-density="{{ $settings['server_density'] }}">
                            <div class="card-body">
                                <div class="gh-server-card__header">
                                    <div class="gh-server-icon">@if(filled($server->iconUrl))<img src="{{ $server->iconUrl }}" alt="">@else<span aria-hidden="true">🖥️</span>@endif</div>
                                    <div class="gh-server-card__copy"><h3 class="h5 mb-1">{{ $server->name }}</h3>@if($settings['show_status'] && filled($game->status))<span class="badge bg-secondary gh-status-badge gh-status--{{ $server->status }}">{{ trans('gaming-hub-core::public.statuses.'.$server->status) }}</span>@endif</div>
                                </div>
                                @if($settings['show_server_descriptions'] && filled($server->shortDescription))<p class="gh-server-card__description mt-3 mb-2">{{ $server->shortDescription }}</p>@endif
                                @if($settings['show_provider_message'] && filled($server->statusMessage))<p class="text-muted small mb-2">{{ $server->statusMessage }}</p>@endif
                                @if($server->currentPlayers !== null)<p class="small mb-2">{{ $server->currentPlayers }}@if($server->maximumPlayers !== null) / {{ $server->maximumPlayers }}@endif players</p>@endif
                                @if(filled($server->sourceLabel))<p class="small text-muted mb-2">Source: {{ $server->sourceLabel }}</p>@endif
                                @if($address)<p class="gh-address small mb-2">{{ $address }}</p>@endif
                                <div class="gh-server-actions">
                                    @if($settings['show_join_button'] && $joinUrl)<a class="btn btn-success" href="{{ $joinUrl }}">{{ trans('gaming-hub-core::public.join') }}</a>@endif
                                    <a class="btn btn-primary" href="{{ route('gaming-hub-core.servers.show', [$server->gameSlug, $server->slug]) }}">{{ trans('gaming-hub-core::public.view_server') }}</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="card gh-empty-state"><div class="card-body"><p class="text-muted mb-0">{{ trans('gaming-hub-core::public.servers_empty') }}</p></div></div>
                    @endforelse
                    </div>
                </section>
            @endif
        </main>
        @if($settings['show_navigation'] && count($navigation))
            <aside><nav class="card gh-game-navigation" aria-label="{{ trans('gaming-hub-core::public.navigation.label') }}"><div class="card-body"><h2 class="h5">{{ trans('gaming-hub-core::public.navigation.label') }}</h2><div class="gh-game-navigation__items">@foreach($navigation as $item)<a href="{{ $item['url'] }}" @class(['active'=>$item['active']]) @if($item['active']) aria-current="page" @endif>@if($item['icon'])<i class="{{ $item['icon'] }}" aria-hidden="true"></i>@endif<span>{{ $item['label'] }}</span></a>@endforeach</div></div></nav></aside>
        @endif
    </div>
</div>
@endsection
