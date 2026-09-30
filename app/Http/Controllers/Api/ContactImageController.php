<?php
namespace App\Http\Controllers\Api;
use App\Models\ContactImage;
use App\Http\Resources\DataCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Support\Glide;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContactImageController extends Controller
{
  protected $contactImage;
  
  /**
   * Constructor
   * 
   * @param ContactImage $contactImage
   */

  public function __construct(ContactImage $contactImage)
  {
    $this->contactImage = $contactImage;
  }

  /**
   * Store a newly added image
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    // Store product image
    $contactImage = ContactImage::create($request->all());
    $contactImage->save();
    return response()->json(['contactImageId' => $contactImage->id]);
  }

  /**
   * Update the order of the given images
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */

  public function order(Request $request)
  {
    $images = $request->get('images');
    foreach($images as $image)
    {
      $i = $this->contactImage->find($image['id']);
      $i->order = $image['order'];
      $i->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given image
   *
   * @param  ContactImage $contactImage
   * @return \Illuminate\Http\Response
   */
  public function toggle(ContactImage $contactImage)
  {
    $contactImage->publish = $contactImage->publish == 0 ? 1 : 0;
    $contactImage->save();
    return response()->json($contactImage->publish);
  }

  /**
   * Update the cropping coords of the specified resource.
   *
   * @param ContactImage $contactImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function coords(ContactImage $contactImage, Request $request)
  {
    $image = $this->contactImage->findOrFail($contactImage->id);
    $image->coords_w = round($request->input('coords_w'), 12);
    $image->coords_h = round($request->input('coords_h'), 12);
    $image->coords_x = round($request->input('coords_x'), 12);
    $image->coords_y = round($request->input('coords_y'), 12);
    $image->save();
    $this->removeCachedImage($image);
    return response()->json('successfully updated');
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  string $image
   * @return \Illuminate\Http\Response
   */
  
  public function destroy($image)
  {
    // Delete image from database
    $record = $this->contactImage->where('name', '=', $image)->first();
    
    if ($record)
    {
      $record->delete();
    }
    
    return response()->json('successfully deleted');
  }

  /**
   * Remove cached version of the image
   *
   * @param ContactImage $contactImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  private function removeCachedImage(ContactImage $contactImage)
  {
    Glide::server()->deleteCache('uploads/' . $contactImage->name);
  }
}
