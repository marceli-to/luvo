<?php
namespace App\Http\Requests;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

/**
 * Requests define their messages as ['field' => ..., 'error' => ...] in
 * errorMessages(). Laravel 12+ only accepts string messages, so the
 * validator gets the 'error' strings and the 422 response is rebuilt in
 * the shape the dashboard reads: errors.{attribute}[] = { field, error }.
 */
abstract class BaseFormRequest extends FormRequest
{
  /**
   * Custom messages as ['rule.key' => ['field' => ..., 'error' => ...]]
   *
   * @return array
   */
  abstract public function errorMessages();

  public function messages()
  {
    return array_map(fn ($message) => $message['error'], $this->errorMessages());
  }

  protected function failedValidation(Validator $validator)
  {
    $errors = [];
    foreach ($validator->errors()->messages() as $attribute => $messages)
    {
      $errors[$attribute] = array_map(fn ($error) => [
        'field' => Str::before($attribute, '.'),
        'error' => $error,
      ], $messages);
    }

    throw new HttpResponseException(response()->json([
      'message' => $validator->errors()->first(),
      'errors' => $errors,
    ], 422));
  }
}
