<?php
namespace App\Http\Requests;

class PublicationStoreRequest extends BaseFormRequest
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
      'team_member_id'  => 'required',
      'title.de'        => 'required',
      'articles.de'     => 'required'
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

      'team_member_id.required' => [
        'field' => 'team_member_id',
        'error' => 'Kategorie wird benötigt!'
      ],

      'title.de.required' => [
        'field' => 'title',
        'error' => 'Titel wird benötigt!'
      ],

      'articles.de.required' => [
        'field' => 'articles',
        'error' => 'Artikel wird benötigt!'
      ],
      
    ];
  }
}
