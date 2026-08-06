<?php
namespace Azuriom\Plugin\GamingHubCore\Http\Requests;
use Azuriom\Plugin\GamingHubCore\Settings\GameDirectorySettings; use Azuriom\Plugin\GamingHubCore\Settings\GamePageSettings; use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
class SaveDirectorySettingsRequest extends FormRequest {
 public function authorize():bool{return $this->user()?->can('gaminghub.settings.manage')??false;}
 public function rules():array{return [
  'title'=>['required','string','max:120'],'description'=>['nullable','string','max:2000'],'columns'=>['required','integer',Rule::in(GameDirectorySettings::COLUMNS)],'density'=>['required',Rule::in(GameDirectorySettings::DENSITIES)],'container_width'=>['required',Rule::in(GameDirectorySettings::WIDTHS)],
  'show_description'=>['sometimes','boolean'],'show_status'=>['sometimes','boolean'],'show_provider_message'=>['sometimes','boolean'],'show_button'=>['sometimes','boolean'],'show_count'=>['sometimes','boolean'],'show_servers_on_game_page'=>['sometimes','boolean'],'fallback_style'=>['required',Rule::in(GameDirectorySettings::FALLBACKS)],
  'show_servers'=>['sometimes','boolean'],'server_density'=>['required',Rule::in(GamePageSettings::DENSITIES)],'server_columns'=>['required','integer',Rule::in(GamePageSettings::COLUMNS)],'show_server_descriptions'=>['sometimes','boolean'],'game_page_show_status'=>['sometimes','boolean'],'game_page_show_provider_message'=>['sometimes','boolean'],'show_join_button'=>['sometimes','boolean'],'show_address'=>['required',Rule::in(GamePageSettings::ADDRESS_MODES)],'show_game_navigation'=>['sometimes','boolean'],
 ];}
 protected function prepareForValidation():void{foreach(['show_description','show_status','show_provider_message','show_button','show_count','show_servers_on_game_page','show_servers','show_server_descriptions','game_page_show_status','game_page_show_provider_message','show_join_button','show_game_navigation'] as $field)$this->merge([$field=>$this->boolean($field)]);}
}
