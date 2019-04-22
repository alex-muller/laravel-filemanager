<?php

namespace AlexMuller\Filemanager\Helpers;

use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\UploadedFile;
use Storage;

class ItemsManager
{

    /**
     * @var FilesystemManager
     */
    private $storage;

    public function __construct()
    {
        /** @var FilesystemManager storage */
        $this->storage = app(FilesystemManager::class)->disk(config('amfm.disk'));
    }

    public function getItems($path, $page, $search)
    {
        $folders = $this->getFolders($path);
        $files   = $this->getFiles($path);

        return $this->formatItems($folders, $files, $page, $search);
    }

    public function remove($paths)
    {
        foreach ($paths as $path) {
            if (is_file($this->storagePath($path))) {
                $this->storage->delete($path);
            } elseif (is_dir($this->storagePath($path))) {
                $this->storage->deleteDirectory($path);
            }
        }

        return true;
    }

    public function rename($path, $newPath)
    {
        return $this->storage->move($path, $newPath);
    }

    protected function getFolders($path)
    {        
        $directories = $this->storage->directories($path);

        return $directories;
    }

    protected function getFiles($path)
    {
        $files = $this->storage->files($path);

        return $files;
    }

    protected function formatItems($folders, $files, $page, $search)
    {
        $folders = $this->searchFilter($folders, $search);
        $files   = $this->searchFilter($files, $search);

        $all_folders_count = count($folders);
        $all_files_count   = count($files);
        //folders
        $limit   = config('amfm.paging') ?: 10;
        $offset  = $limit * ($page - 1);
        $folders = array_slice($folders, $offset, $limit);
        $folders = $this->getFormatedItems($folders);
        //files
        $file_limit  = $limit - count($folders);
        $file_offset = ($offset - $all_folders_count) > 0 ? $offset - $all_folders_count : 0;
        $files       = array_slice($files, $file_offset, $file_limit);
        $files       = $this->getFormatedItems($files);
        //pagination
        $pages = ceil(($all_folders_count + $all_files_count) / $limit);
        $items = array_merge($folders, $files);

        return [
            'items'      => $items,
            'pagination' => [
                'page'  => $page,
                'pages' => $pages,
            ],
        ];
    }

    protected function searchFilter($items, $search)
    {
        if ($search) {
            $items = array_filter(
                $items,
                function ($item) use ($search) {
                    $paths = explode('/', $item);
                    $name  = array_pop($paths);
                    if (stripos($name, $search) !== false) {
                        return $item;
                    }

                    return false;
                }
            );
        }

        return $items;
    }

    protected function getFormatedItems($items)
    {
        $items = array_map(
            function ($path) {
                $paths = explode(DIRECTORY_SEPARATOR, $path);
                $name  = array_pop($paths);
                $_item = [
                    'path' => $path,
                    'name' => $name,
                    'type' => $this->getItemType($path),
                ];

                return $_item;
            },
            $items
        );

        return $items;
    }

    protected function getItemType($path)
    {

        $type = mime_content_type($this->storagePath($path));

        if ($type == 'directory') {
            return $type;
        }

        $file_types = config('amfm.file_types') ?: [];

        foreach ($file_types as $file_type => $mimes) {
            foreach ($mimes as $mime) {
                $type = $mime == $type ? $file_type : $type;
            }
        }

        return $type;
    }

    public function storagePath($path = '')
    {
        return $this->storage->getDriver()->getAdapter()->getPathPrefix() . '/' . $path;
    }

    public function createDirectory($name, $path)
    {
        return $this->storage->makeDirectory($path . DIRECTORY_SEPARATOR . $name);
    }

    public function storeFiles($files, $path)
    {
        foreach ($files as $file){
            /** @var UploadedFile $file */
            $name = $file->getClientOriginalName();
            /* $ext = $file->getClientOriginalExtension();
             $ext = $ext ? '.'.$ext : '';*/
            $file->storeAs($path, $name, ['disk' => config('amfm.disk')]);
        }
        return true;
    }
}
