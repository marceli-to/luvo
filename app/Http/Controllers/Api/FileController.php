<?php
namespace App\Http\Controllers\Api;
use App\Models\File;
use App\Http\Resources\DataCollection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class FileController extends Controller
{
  protected $file;
  
  /**
   * Constructor
   * 
   * @param File $file
   */

  public function __construct(File $file)
  {
    $this->file = $file;
  }

  /**
   * Get a list of files
   * 
   * @return \Illuminate\Http\Response
   */
  public function get()
  {
    $files = $this->file->get();
    $file_list = [];
    foreach($files as $file)
    {
      $file_list[] = [
        'title' => $file->name,
        'value' => '/storage/uploads/files/' . $file->name,
      ];
      $file_info = pathinfo($file);
    }

    return response()->json($file_list);
  }

  /**
   * Store a newly uploaded file
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $file = File::create($request->all());
    $file->save();
    return response()->json(['id' => $file->id]);
  }


  /**
   * Remove the specified resource from storage.
   *
   * @param File $file
   * @return \Illuminate\Http\Response
   */
  
  public function destroy(File $file)
  {
    // Delete image from database
    $record = $this->file->findOrFail($file->id);
    if ($record)
    {
      $record->delete();
    }
    return response()->json('successfully deleted');
  }

}
