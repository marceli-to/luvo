<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserChangePasswordRequest;

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
    return response()->json(['firstname' => $user->firstname, 'name' => $user->name, 'id' => $user->id]);
  }

  /**
   * Change a users password
   * 
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function updatePassword(UserChangePasswordRequest $request)
  {
    $user = $this->user->findOrFail(auth()->user()->id);
    $user->password = Hash::make($request->password);
    $user->save();
    return response()->json('successfully updated');
  }

}
