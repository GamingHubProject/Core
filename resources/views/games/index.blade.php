@extends('layouts.app')

@section('title', $settings['title'])

@section('content')
<style>
/* Gaming Hub Core directory rendering contract. Themes may restyle these
   selectors but must preserve their semantic meaning. */
.gh-page-container,.gh-page-container-wide,.gh-page-container-full{width:100%;margin-inline:auto;padding-inline:clamp(12px,2.5vw,32px)}
.gh-page-container{max-width:1140px}.gh-page-container-wide{max-width:1600px}.gh-page-container-full{max-width:none}
.gh-games-grid{display:grid;gap:1.5rem;grid-template-columns:1fr;align-items:stretch}
.gh-games-grid>*{min-width:0}
.gh-game-card{display:flex;flex-direction:column;min-width:0;height:100%;overflow:hidden}
.gh-game-card--compact{min-height:210px}.gh-game-card--standard{min-height:330px}
.gh-game-card--compact .gh-game-card__body{padding:1rem}.gh-game-card--standard .gh-game-card__body{padding:1.5rem}
.gh-game-card--compact .gh-game-card__header{margin-bottom:.75rem;gap:.75rem}.gh-game-card--standard .gh-game-card__header{margin-bottom:1.25rem;gap:1rem}
.gh-game-card--compact .gh-game-description,.gh-game-card--compact .gh-game-message{margin-bottom:.75rem;line-height:1.4}
.gh-game-card--standard .gh-game-description,.gh-game-card--standard .gh-game-message{margin-bottom:1.15rem;line-height:1.6}
.gh-game-media{aspect-ratio:16/7;overflow:hidden;background:var(--bs-secondary-bg,#e9ecef)}
.gh-game-card--compact .gh-game-media{aspect-ratio:16/5}.gh-game-card--standard .gh-game-media{aspect-ratio:16/8}
.gh-game-media img{display:block;width:100%;height:100%;object-fit:cover}
.gh-game-icon{width:56px;height:56px;flex:0 0 56px;object-fit:cover}
.gh-game-card--compact .gh-game-icon{width:48px;height:48px;flex-basis:48px}
.gh-game-description,.gh-game-message{overflow-wrap:anywhere}
@media(min-width:768px){
  .gh-games-grid--2,.gh-games-grid--3,.gh-games-grid--4{grid-template-columns:repeat(2,minmax(0,1fr))}
}
@media(min-width:1200px){
  .gh-games-grid--1{grid-template-columns:minmax(0,1fr)}
  .gh-games-grid--2{grid-template-columns:repeat(2,minmax(0,1fr))}
  .gh-games-grid--3{grid-template-columns:repeat(3,minmax(0,1fr))}
  .gh-games-grid--4{grid-template-columns:repeat(4,minmax(0,1fr))}
}
@media(max-width:767.98px){.gh-game-card--compact,.gh-game-card--standard{min-height:0}}
</style>

<div class="{{ $settings['container_class'] }} content py-4 gh-games-directory {{ $settings['fallback_class'] }}"
     data-gh-columns="{{ $settings['columns'] }}"
     data-gh-density="{{ $settings['density'] }}"
     data-gh-container="{{ $settings['container_width'] }}"
     data-gh-fallback="{{ $settings['fallback_style'] }}">
    <header class="mb-4">
        <div class="d-flex flex-wrap align-items-baseline gap-2">
            <h1 class="mb-0">{{ $settings['title'] }}</h1>
            @if($settings['show_count'])<span class="text-muted">({{ $games->count() }})</span>@endif
        </div>
        @if($settings['description'] !== '')
            <div class="text-muted mt-2">{!! nl2br(e($settings['description'])) !!}</div>
        @endif
    </header>

    @if($games->isEmpty())
        <div class="alert alert-secondary">{{ trans('gaming-hub-core::public.empty') }}</div>
    @else
        <div class="gh-games-grid {{ $settings['grid_class'] }}">
            @foreach($games as $game)
                @php
                    $hasBanner = is_string($game->bannerUrl) && trim($game->bannerUrl) !== '';
                    $showMedia = $hasBanner || $settings['fallback_style'] === 'media';
                @endphp
                <article class="card shadow-sm gh-game-card {{ $settings['card_class'] }}" data-gh-has-banner="{{ $hasBanner ? '1' : '0' }}">
                    @if($showMedia)
                        <div class="gh-game-media" data-gh-media="{{ $hasBanner ? 'banner' : 'placeholder' }}">
                            <img src="{{ $hasBanner ? $game->bannerUrl : asset('assets/plugins/gaming-hub-core/img/game-placeholder.svg') }}" alt="" loading="lazy">
                        </div>
                    @endif
                    <div class="card-body d-flex flex-column gh-game-card__body">
                        <div class="d-flex align-items-center gh-game-card__header">
                            @if($game->iconUrl)
                                <img src="{{ $game->iconUrl }}" class="rounded gh-game-icon" alt="" loading="lazy">
                            @else
                                <div class="rounded bg-body-secondary d-flex align-items-center justify-content-center gh-game-icon" aria-hidden="true"><i class="bi bi-controller fs-3"></i></div>
                            @endif
                            <div class="min-w-0">
                                <h2 class="h5 mb-1 text-break">{{ $game->name }}</h2>
                                @if($settings['show_status'])
                                    <span class="badge {{ $game->status === 'online' ? 'bg-success' : ($game->status === 'maintenance' ? 'bg-warning text-dark' : ($game->status === 'offline' ? 'bg-danger' : 'bg-secondary')) }}">
                                        {{ trans('gaming-hub-core::public.statuses.'.$game->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        @if($settings['show_description'])
                            <p class="text-muted gh-game-description">{{ $game->shortDescription ?: trans('gaming-hub-core::public.description_missing') }}</p>
                        @endif
                        @if($settings['show_provider_message'] && $game->statusMessage)
                            <p class="gh-game-message">{{ $game->statusMessage }}</p>
                        @endif
                        @if($settings['show_button'])
                            <a class="btn btn-primary mt-auto" href="{{ route('gaming-hub-core.games.show', $game->slug) }}">{{ trans('gaming-hub-core::public.view') }}</a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
