<?php
namespace AlexMuller\Filemanager\Helpers;

use Storage;
use Response;


class Item
{
    protected $file_path;

    public function __construct($file_path)
    {
        $this->file_path = $file_path;
    }

    public function responseImageOrFile()
    {
        $file_path = $this->file_path;

        if (! Storage::exists($file_path)) {
            abort(404);
        }

        $file = Storage::get($file_path);
        $type = mime_content_type( Items::storagePath($file_path));

        $response = Response::make($file);
        $response->header('Content-Type', $type);

        return $response;
    }

    public static function createDirectory($name, $path)
    {
        return Storage::makeDirectory($path . DIRECTORY_SEPARATOR . $name);
    }

    public static function storeFiles($files, $path)
    {
      foreach ($files as $file){
          $name = $file->getClientOriginalName();
         /* $ext = $file->getClientOriginalExtension();
          $ext = $ext ? '.'.$ext : '';*/
          $file->storeAs($path, $name);
      }
      return true;
    }
}