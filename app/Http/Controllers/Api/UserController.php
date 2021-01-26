<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function __construct(User $user)
  {
    $this->user = $user;
  }

  /**
   * Get users info by authenticated user
   */
  public function find()
  {
    $user = $this->user->findOrFail(auth()->user()->id);
    return response()->json(['firstname' => $user->firstname, 'name' => $user->name]);
  }

  /**
   * Change a users email address
   * 
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function updateEmail(UserChangeEmailRequest $request)
  {
    $user = $this->user->findOrFail(auth()->user()->id);
    $user->email = $request->email;
    $user->email_verified_at = null;
    $user->sendEmailVerificationNotification();
    $user->save();
    return response()->json('successfully updated');
  }

}
