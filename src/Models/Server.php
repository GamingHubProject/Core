<?php
namespace Azuriom\Plugin\GamingHubCore\Models;
use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class Server extends Model {
 use HasTablePrefix; protected $prefix='gaminghub_';
 protected $fillable=['game_id','name','slug','short_description','long_description','icon_url','banner_url','enabled','public','position','hostname','display_port','join_url'];
 protected $casts=['game_id'=>'integer','enabled'=>'boolean','public'=>'boolean','position'=>'integer','display_port'=>'integer'];
 protected static function booted(): void { static::creating(fn(Server $s)=>$s->uuid ??= (string)Str::uuid()); }
 public function game(): BelongsTo { return $this->belongsTo(Game::class); }
 public function providers(): HasMany { return $this->hasMany(ProviderInstance::class); }
 public function publicDataPolicies(): HasMany { return $this->hasMany(PublicDataPolicy::class); }
 public function scopeEnabled(Builder $q): Builder { return $q->where('enabled',true); }
 public function scopePublic(Builder $q): Builder { return $q->where('public',true); }
 public function scopeOrdered(Builder $q): Builder { return $q->orderBy('position')->orderBy('name')->orderBy('id'); }
}
