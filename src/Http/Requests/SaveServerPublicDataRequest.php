<?php
namespace Azuriom\Plugin\GamingHubCore\Http\Requests; use Illuminate\Foundation\Http\FormRequest; use Illuminate\Validation\Rule;
final class SaveServerPublicDataRequest extends FormRequest { public function authorize():bool{return $this->user()?->can('gaminghub.settings.manage')??false;} public function rules():array{return ['visibility'=>['required','array'],'visibility.*'=>['required',Rule::in(['inherit','show','hide'])],'attribution'=>['required','array'],'attribution.*'=>['required',Rule::in(['inherit','show','hide'])]];} }
