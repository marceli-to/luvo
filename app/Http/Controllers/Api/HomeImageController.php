<?php
namespace App\Http\Controllers\Api;
use App\Models\HomeImage;
use App\Http\Resources\DataCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Support\Glide;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeImageController extends Controller
{
  protected $homeImage;
  
  /**
   * Constructor
   * 
   * @param HomeImage $homeImage
   */

  public function __construct(HomeImage $homeImage)
  {
    $this->homeImage = $homeImage;
  }

  /**
   * Store a newly added News image
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    // Store product image
    $homeImage = HomeImage::create($request->all());
    $homeImage->save();
    return response()->json(['homeImageId' => $homeImage->id]);
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
      $i = $this->homeImage->find($image['id']);
      $i->order = $image['order'];
      $i->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given image
   *
   * @param  HomeImage $homeImage
   * @return \Illuminate\Http\Response
   */
  public function toggle(HomeImage $homeImage)
  {
    $homeImage->publish = $homeImage->publish == 0 ? 1 : 0;
    $homeImage->save();
    return response()->json($homeImage->publish);
  }

  /**
   * Update the cropping coords of the specified resource.
   *
   * @param HomeImage $homeImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function coords(HomeImage $homeImage, Request $request)
  {
    $image = $this->homeImage->findOrFail($homeImage->id);
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
    $record = $this->homeImage->where('name', '=', $image)->first();
    
    if ($record)
    {
      $record->delete();
    }
    
    return response()->json('successfully deleted');
  }

  /**
   * Remove cached version of the image
   *
   * @param HomeImage $homeImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  private function removeCachedImage(HomeImage $homeImage)
  {
    Glide::server()->deleteCache('uploads/' . $homeImage->name);
  }
}
