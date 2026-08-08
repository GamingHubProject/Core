<?php
namespace Azuriom\Plugin\GamingHubCore\Models;
use Azuriom\Models\Traits\HasTablePrefix; use Illuminate\Database\Eloquent\Model; use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PublicDataPolicy extends Model { use HasTablePrefix; protected $prefix='gaminghub_'; protected $table='gaminghub_public_data_policies'; protected $fillable=['server_id','stat_key','visibility','attribution']; public function server():BelongsTo{return $this->belongsTo(Server::class);} }
