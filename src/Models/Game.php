<?php
namespace Azuriom\Plugin\GamingHubCore\Models;
use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class Game extends Model {
 use HasTablePrefix; protected $prefix='gaminghub_';
 protected $fillable=['slug','name','short_name','description','short_description','long_description','icon_url','banner_url','icon_media_id','cover_media_id','accent_color','enabled','sort_order'];
 protected $casts=['enabled'=>'boolean','icon_media_id'=>'integer','cover_media_id'=>'integer','sort_order'=>'integer'];
 protected static function booted(): void { static::creating(fn(Game $g)=>$g->uuid ??= (string)Str::uuid()); }
 public function servers(): HasMany { return $this->hasMany(Server::class); }
 /** @deprecated Providers are server-owned in v0.4.0. */ public function providers(): HasMany { return $this->hasMany(ProviderInstance::class); }
 public function scopeEnabled(Builder $q): Builder { return $q->where('enabled',true); }
 public function scopeOrdered(Builder $q): Builder { return $q->orderBy('sort_order')->orderBy('name')->orderBy('id'); }
}
