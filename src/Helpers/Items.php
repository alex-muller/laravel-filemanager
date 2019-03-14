<?php

namespace AlexMuller\Filemanager\Helpers;

use Storage;

class Items
{

    public static function getItems($path, $page, $search)
    {
        $folders = self::getFolders($path);
        $files   = self::getFiles($path);

        return self::formatItems($folders, $files, $page, $search);
    }

    public static function remove($paths)
    {
        foreach ($paths as $path) {
            if (is_file(self::storagePath($path))) {
                Storage::delete($path);
            } elseif (is_dir(self::storagePath($path))) {
                Storage::deleteDirectory($path);
            }
        }

        return true;
    }

    public static function rename($path, $newPath)
    {
        return Storage::move($path, $newPath);
    }

    protected static function getFolders($path)
    {
        $directories = Storage::directories($path);

        return $directories;
    }

    protected static function getFiles($path)
    {
        $files = Storage::files($path);

        return $files;
    }

    protected static function formatItems($folders, $files, $page, $search)
    {
        $folders = self::searchFilter($folders, $search);
        $files   = self::searchFilter($files, $search);

        $all_folders_count = count($folders);
        $all_files_count   = count($files);
        //folders
        $limit   = config('amfm.paging') ?: 10;
        $offset  = $limit * ($page - 1);
        $folders = array_slice($folders, $offset, $limit);
        $folders = self::getFormatedItems($folders);
        //files
        $file_limit  = $limit - count($folders);
        $file_offset = ($offset - $all_folders_count) > 0 ? $offset - $all_folders_count : 0;
        $files       = array_slice($files, $file_offset, $file_limit);
        $files       = self::getFormatedItems($files);
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

    protected static function searchFilter($items, $search)
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

    protected static function getFormatedItems($items)
    {
        $items = array_map(
            function ($path) {
                $paths = explode(DIRECTORY_SEPARATOR, $path);
                $name  = array_pop($paths);
                $_item = [
                    'path' => $path,
                    'name' => $name,
                    'type' => self::getItemType($path),
                ];

                return $_item;
            },
            $items
        );

        return $items;
    }

    protected static function getItemType($path)
    {

        $type = mime_content_type(self::storagePath($path));

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

    public static function storagePath($path = '')
    {
        return Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix() . '/' . $path;
    }
}
