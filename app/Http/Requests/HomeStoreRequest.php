<?php
namespace App\Http\Requests;

class HomeStoreRequest extends BaseFormRequest
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
      'title.de' => 'required',
      'text.de'  => 'required'
    ];
  }

  /**
   * Custom message for validation
   *
   * @return array
   */
  public function errorMessages()
  {
    return [
      
      'title.de.required' => [
        'field' => 'title',
        'error' => 'Titel wird benötigt!'
      ],

      'text.de.required' => [
        'field' => 'text',
        'error' => 'Text wird benötigt!'
      ],
      
    ];
  }
}
