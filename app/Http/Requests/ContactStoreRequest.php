<?php
namespace App\Http\Requests;

class ContactStoreRequest extends BaseFormRequest
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
      'address.de' => 'required',
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

      'address.de.required' => [
        'field' => 'address',
        'error' => 'Adresse wird benötigt!'
      ],
    ];
  }
}
