<?php

namespace AlexMuller\Filemanager\Helpers;

use Illuminate\Filesystem\FilesystemManager;
use Storage;
use Response;


class Item
{
    protected $file_path;

    /** @var FilesystemManager */
    private $storage;

    private $itemsManager;

    public function __construct($file_path)
    {
        $this->itemsManager = new ItemsManager();
        $this->file_path   = $file_path;
        $this->storage     = app(FilesystemManager::class)->disk(config('amfm.disk'));
    }

    public function responseImageOrFile()
    {
        $file_path = $this->file_path;

        if (!$this->storage->exists($file_path)) {
            abort(404);
        }

        $file = $this->storage->get($file_path);
        $type = mime_content_type($this->itemsManager->storagePath($file_path));

        $response = Response::make($file);
        $response->header('Content-Type', $type);

        return $response;
    }


}