<?php
namespace App\Http\Controllers\Api;
use App\Models\File;
use App\Http\Resources\DataCollection;
use App\Http\Controllers\Controller;
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
    return new DataCollection($this->file->get());
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

}
