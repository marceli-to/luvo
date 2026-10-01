<?php
namespace App\Http\Requests;

class UserChangePasswordRequest extends BaseFormRequest
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
      'password' => 'required|min:6',
      'password_confirm' => 'required|same:password',
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
      'password.required' => [
        'field' => 'password',
        'error' => 'Passwort wird benötigt!'
      ],
      'password.min' => [
        'field' => 'password',
        'error' => 'Passwort zu kurz!'
      ],
      'password_confirm.required' => [
        'field' => 'password_confirm',
        'error' => 'Passwort wird benötigt!'
      ],
      'password_confirm.same' => [
        'field' => 'password_confirm',
        'error' => 'Passwörter stimmen nicht überein!'
      ],
    ];
  }
}
