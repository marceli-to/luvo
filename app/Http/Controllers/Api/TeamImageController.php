<?php
namespace App\Http\Controllers\Api;
use App\Models\TeamImage;
use App\Support\Glide;
use App\Support\Uploads;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TeamImageController extends Controller
{
  protected $teamImage;
  
  /**
   * Constructor
   * 
   * @param TeamImage $teamImage
   */

  public function __construct(TeamImage $teamImage)
  {
    $this->teamImage = $teamImage;
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
    $teamImage = TeamImage::create($request->all());
    $teamImage->save();
    return response()->json(['teamImageId' => $teamImage->id]);
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
      $i = $this->teamImage->find($image['id']);
      $i->order = $image['order'];
      $i->save(); 
    }
    return response()->json('successfully updated');
  }

  /**
   * Toggle the status a given image
   *
   * @param  TeamImage $teamImage
   * @return \Illuminate\Http\Response
   */
  public function toggle(TeamImage $teamImage)
  {
    $teamImage->publish = $teamImage->publish == 0 ? 1 : 0;
    $teamImage->save();
    return response()->json($teamImage->publish);
  }

  /**
   * Update the cropping coords of the specified resource.
   *
   * @param TeamImage $teamImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function coords(TeamImage $teamImage, Request $request)
  {
    $image = $this->teamImage->findOrFail($teamImage->id);
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
    $record = $this->teamImage->where('name', '=', $image)->first();
    
    // With its upload and rendered variants
    if ($record)
    {
      Uploads::deleteImages([$record]);
    }
    
    return response()->json('successfully deleted');
  }

  /**
   * Remove cached version of the image
   *
   * @param TeamImage $teamImage
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  private function removeCachedImage(TeamImage $teamImage)
  {
    Glide::server()->deleteCache('uploads/' . $teamImage->name);
  }
}
