<?php
namespace App\Http\Controllers\Api;
use App\Models\AssistantImage;
use App\Http\Resources\DataCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AssistantImageController extends Controller
{
  protected $assistantImage;
  
  /**
   * Constructor
   * 
   * @param AssistantImage $assistantImage
   */

  public function __construct(AssistantImage $assistantImage)
  {
    $this->assistantImage = $assistantImage;
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
    $assistantImage = AssistantImage::create($request->all());
    $assistantImage->save();
    return response()->json(['assistantImageId' => $assistantImage->id]);
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
      $i = $this->assistantImage->find($image['id']);
      $i->order = $image['order'];
      $i->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given image
   *
   * @param  AssistantImage $assistantImage
   * @return \Illuminate\Http\Response
   */
  public function toggle(AssistantImage $assistantImage)
  {
    $assistantImage->publish = $assistantImage->publish == 0 ? 1 : 0;
    $assistantImage->save();
    return response()->json($assistantImage->publish);
  }

  /**
   * Update the cropping coords of the specified resource.
   *
   * @param AssistantImage $assistantImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function coords(AssistantImage $assistantImage, Request $request)
  {
    $image = $this->assistantImage->findOrFail($assistantImage->id);
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
    $record = $this->assistantImage->where('name', '=', $image)->first();
    
    if ($record)
    {
      $record->delete();
    }
    
    return response()->json('successfully deleted');
  }

  /**
   * Remove cached version of the image
   *
   * @param AssistantImage $assistantImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  private function removeCachedImage(AssistantImage $assistantImage)
  {
    // Get an instance of the ImageCache class
    $imageCache = new \Intervention\Image\ImageCache();

    // Get a cached image from it and apply all of your templates / methods
    $image = $imageCache->make(storage_path('app/public/uploads/') . $assistantImage->name)->filter(new \App\Filters\Image\Template\Cache);

    // Remove the image from the cache by using its internal checksum
    Cache::forget($image->checksum());
  }
}
