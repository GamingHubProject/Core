<?php

namespace Azuriom\Plugin\GamingHubCore\Navigation;

use Azuriom\Plugin\GamingHubCore\Exceptions\DuplicateGameNavigationItem;
use Azuriom\Plugin\GamingHubCore\Models\Game;
use Illuminate\Http\Request;
use Throwable;

final class GameNavigation
{
    /** @var array<string, GameNavigationItem> */
    private array $items = [];

    public function register(GameNavigationItem $item): void
    {
        if (isset($this->items[$item->id])) {
            throw DuplicateGameNavigationItem::forId($item->id);
        }

        $this->items[$item->id] = $item;
    }

    /** @return list<array{id:string,label:string,url:string,icon:?string,order:int,active:bool}> */
    public function visibleFor(Game $game, Request $request): array
    {
        $visible = [];

        foreach ($this->items as $item) {
            try {
                if ($item->visible !== null && ! ($item->visible)($game, $request)) {
                    continue;
                }

                $url = trim((string) ($item->url)($game));
                if ($url === '') {
                    continue;
                }

                $visible[] = [
                    'id' => $item->id,
                    'label' => $item->label,
                    'url' => $url,
                    'icon' => $item->icon,
                    'order' => $item->order,
                    'active' => $item->active !== null && (bool) ($item->active)($game, $request),
                ];
            } catch (Throwable) {
                // A broken optional contribution must not break the public game page.
            }
        }

        usort($visible, static fn (array $a, array $b): int => [$a['order'], $a['label'], $a['id']] <=> [$b['order'], $b['label'], $b['id']]);

        return $visible;
    }
}
