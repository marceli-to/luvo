<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class AssistantStoreRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'description.de' => 'required',
      'team_id'   => 'required'
    ];
  }

  /**
   * Custom message for validation
   *
   * @return array
   */
  public function messages()
  {
    return [

      'description.de.required' => [
        'field' => 'description',
        'error' => 'Beschreibung wird benötigt!'
      ],
      
      'team_id.required' => [
        'field' => 'team_id',
        'error' => 'Team wird benötigt!'
      ],
    ];
  }
}
