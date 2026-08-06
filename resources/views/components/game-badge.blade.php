<span {{ $attributes->class(['badge d-inline-flex align-items-center gap-1']) }}
      style="background-color: {{ $game->accent_color }}; color: {{ color_contrast($game->accent_color) }};">
    <i class="bi bi-controller" aria-hidden="true"></i>
    <span>{{ $game->name }}</span>
</span>
