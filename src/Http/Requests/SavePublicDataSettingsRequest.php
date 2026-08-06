<?php
namespace Azuriom\Plugin\GamingHubCore\Http\Requests; use Illuminate\Foundation\Http\FormRequest;
final class SavePublicDataSettingsRequest extends FormRequest { public function authorize():bool{return $this->user()?->can('gaminghub.settings.manage')??false;} public function rules():array{return ['visible'=>['required','array'],'visible.*'=>['boolean'],'attribution'=>['required','array'],'attribution.*'=>['boolean']];} }
