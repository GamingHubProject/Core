<?php
namespace Azuriom\Plugin\GamingHubCore\Http\Requests;
use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
class SaveServerRequest extends FormRequest {
 public function authorize(): bool { return $this->user()?->can('gaminghub.servers.manage')===true; }
 public function rules(): array { $game=$this->route('game'); $server=$this->route('server'); return [
  'name'=>['required','string','max:255',Rule::unique('gaminghub_servers','name')->where(fn($q)=>$q->where('game_id',$game->id))->ignore($server?->id)], 'slug'=>['required','string','max:255','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',Rule::unique('gaminghub_servers','slug')->where(fn($q)=>$q->where('game_id',$game->id))->ignore($server?->id)],
  'short_description'=>['nullable','string','max:500'],'long_description'=>['nullable','string','max:20000'],'icon_url'=>['nullable','url','max:2048'],'banner_url'=>['nullable','url','max:2048'],
  'enabled'=>['required','boolean'],'public'=>['required','boolean'],'position'=>['required','integer','min:-2147483648','max:2147483647'],
  'hostname'=>['nullable','string','max:253','regex:/^(?=.{1,253}$)(?!-)[A-Za-z0-9.-]+(?<!-)$/'],'display_port'=>['nullable','integer','between:1,65535'],'join_url'=>['nullable','url','max:2048'],
 ]; }
 protected function prepareForValidation(): void { $this->merge(['enabled'=>$this->boolean('enabled'),'public'=>$this->boolean('public'),'short_description'=>$this->nullIfBlank('short_description'),'long_description'=>$this->nullIfBlank('long_description'),'hostname'=>$this->nullIfBlank('hostname'),'display_port'=>$this->nullIfBlank('display_port'),'join_url'=>$this->nullIfBlank('join_url'),'icon_url'=>$this->nullIfBlank('icon_url'),'banner_url'=>$this->nullIfBlank('banner_url')]); }
 private function nullIfBlank(string $key): mixed { $v=$this->input($key); return is_string($v)&&trim($v)===''?null:$v; }
}
