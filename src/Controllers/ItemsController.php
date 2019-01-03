<?php

namespace AlexMuller\Filemanager\Controllers;


use AlexMuller\Filemanager\Helpers\Item;
use AlexMuller\Filemanager\Helpers\Items;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ItemsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except('getItem');
    }

    public function getItems()
    {
        $path   = request('path') ?: config('amfm.path');
        $page   = request('page') ?: 1;
        $search = request('search') ?: '';
        $items  = Items::getItems($path, $page, $search);

        return response()->json($items);
    }

    public function createDirectory()
    {
        if (Item::createDirectory(request('name'), request('path'))) {
            return response()->json(
                [
                    'status'  => 'success',
                    'message' => 'Folder ' . request('name') . ' was created',
                ]
            );
        }

        return response()->json(
            [
                'status'  => 'error',
                'message' => 'error',
            ]
        );
    }

    public function upload(Request $request)
    {
        $files = $request->file('files');
        $path  = $request->path;
        if (Item::storeFiles($files, $path)) {
            return response()->json(
                [
                    'status'  => 'success',
                    'message' => 'Files was successfully uploaded ',
                ]
            );
        }

        return response()->json(
            [
                'status'  => 'error',
                'message' => 'error',
            ]
        );
    }

    public function remove(Request $request)
    {
        if (Items::remove(request('paths'))) {
            return response()->json(
                [
                    'status'  => 'success',
                    'message' => '"Items" was deleted',
                ]
            );
        }

        return response()->json(
            [
                'status'  => 'error',
                'message' => 'error',
            ]
        );
    }

    public function getItem($filename)
    {
        $item = new Item($filename);

        return $item->responseImageOrFile();
    }
}
