<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;

class UploadController extends Controller
{
  protected $upload_path;

  protected $prefix = 'sajo-';
  
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->upload_path = storage_path('app/public/uploads');
    if (!File::isDirectory($this->upload_path))
    {
      File::makeDirectory($this->upload_path, 0775, true, true);
    }
  }

  /**
   * Image upload
   * 
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function image(Request $request)
  {
    $file = $request->file('file');
    $name = $this->sanitize(trim($file->getClientOriginalName()));
    $name = $this->prefix . uniqid()  . '_' . $name;
    $file->move($this->upload_path, $name);
    $filetype = \File::extension($this->upload_path . $name);

    $image_types = ['jpg', 'jpeg', 'png'];
    $orientation = '';

    if (in_array($filetype, $image_types))
    {
      $img = \Image::make(storage_path('app/public/uploads/') . $name);
      $orientation = $img->width() >= $img->height() ? 'l' : 'p';
    }
    
    return response()->json(['name' => $name, 'filetype' => $filetype, 'orientation' => $orientation], 200);
  }

  /**
   * File upload
   * 
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function file(Request $request)
  {
    $file = $request->file('file');
    $name = $this->sanitize(trim($file->getClientOriginalName()));
    $name = $this->prefix . uniqid()  . '_' . $name;
    $file->move($this->upload_path, $name);
    $filetype = File::extension($this->upload_path . $name);
    $filesize = File::size($this->upload_path . '/' . $name);
    return response()->json(['name' => $name, 'type' => $filetype, 'size' => $this->filesize($filesize)], 200);
  }
  
  public function getFiles()
  { 
    $allowed_files_extensions = [
      'pdf', 'doc', 'docx', 'zip', 'tar', 'png'
    ]; 

    // Filelist
    $file_list = [];

    // Get all Files
    $files = Storage::files('public/uploads');

    foreach($files as $file)
    {
      $file_info = pathinfo($file);

      if (in_array($file_info['extension'], $allowed_files_extensions))
      {
        $f = basename($file);
        $file_list[] = [
          'title' => $f,
          'value' => '/storage/uploads/' . $f,
        ];
      }
    }

    return response()->json($file_list);

  }

  /**
   * Sanitize a filename
   *
   * @param str $filename
   * @param boolean  $force_lowercase - Force the string to lowercase?
   * @param boolean  $anal - If set to *true*, will remove all non-alphanumeric characters.
   */

  protected function sanitize($filename, $force_lowercase = true, $anal = true)
  {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "=", "+", "[", "{", "]", "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;", "â€”", "â€“", ",", "<", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($filename)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9._\-]/", "", $clean) : $clean ;
    return ($force_lowercase) ? (function_exists('mb_strtolower')) ? mb_strtolower($clean, 'UTF-8') : strtolower($clean) : $clean;
  }

  /**
   * Get the formatted size of a file
   *
   * @param $size
   * @return Number $size
   */

  protected function filesize($size)
  {
    $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
  }
}
