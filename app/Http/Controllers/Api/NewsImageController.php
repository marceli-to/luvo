<?php
namespace App\Http\Controllers\Api;
use App\Models\NewsImage;
use App\Http\Resources\DataCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NewsImageController extends Controller
{
  protected $newsImage;
  
  /**
   * Constructor
   * 
   * @param NewsImage $newsImage
   */

  public function __construct(NewsImage $newsImage)
  {
    $this->newsImage = $newsImage;
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
    $newsImage = NewsImage::create($request->all());
    $newsImage->save();
    return response()->json(['newsImageId' => $newsImage->id]);
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
      $i = $this->newsImage->find($image['id']);
      $i->order = $image['order'];

      if ($i->preview)
      {
        $i->order = -1;
      }

      $i->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given image
   *
   * @param  NewsImage $newsImage
   * @return \Illuminate\Http\Response
   */
  public function toggle(NewsImage $newsImage)
  {
    $newsImage->publish = $newsImage->publish == 0 ? 1 : 0;
    $newsImage->save();
    return response()->json($newsImage->publish);
  }

  /**
   * Update the cropping coords of the specified resource.
   *
   * @param NewsImage $newsImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function coords(NewsImage $newsImage, Request $request)
  {
    $image = $this->newsImage->findOrFail($newsImage->id);
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
    $record = $this->newsImage->where('name', '=', $image)->first();
    
    if ($record)
    {
      $record->delete();
    }
    
    return response()->json('successfully deleted');
  }

  /**
   * Remove cached version of the image
   *
   * @param NewsImage $newsImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  private function removeCachedImage(NewsImage $newsImage)
  {
    // Get an instance of the ImageCache class
    $imageCache = new \Intervention\Image\ImageCache();

    // Get a cached image from it and apply all of your templates / methods
    $image = $imageCache->make(storage_path('app/public/uploads/') . $newsImage->name)->filter(new \App\Filters\Image\Template\Cache);

    // Remove the image from the cache by using its internal checksum
    Cache::forget($image->checksum());
  }
}
