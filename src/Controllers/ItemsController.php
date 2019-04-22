<?php

namespace AlexMuller\Filemanager\Controllers;


use AlexMuller\Filemanager\Helpers\Item;
use AlexMuller\Filemanager\Helpers\Items;
use AlexMuller\Filemanager\Helpers\ItemsManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class ItemsController extends Controller
{

    private $itemsManager;

    public function __construct()
    {
        $this->middleware(config('amfm.middleware'))->except('getItem');

        $this->itemsManager = new ItemsManager();
    }

    public function getItems()
    {
        $path   = request('path');
        $page   = request('page') ?: 1;
        $search = request('search') ?: '';
        $items  = $this->itemsManager->getItems($path, $page, $search);

        return response()->json($items);
    }

    public function createDirectory()
    {
        if ($this->itemsManager->createDirectory(request('name'), request('path'))) {
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
        if ($this->itemsManager->storeFiles($files, $path)) {
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

    public function updateItem(Request $request)
    {
        $path = $request->path . DIRECTORY_SEPARATOR . $request->name;
        $newPath = $request->path . DIRECTORY_SEPARATOR . $request->newName;
        $this->itemsManager->rename($path, $newPath);

        return response()->json(
            [
                'status'  => 'success',
                'message' => 'Item was renamed',
            ]
        );
    }

    public function remove(Request $request)
    {
        if ($this->itemsManager->remove(request('paths'))) {
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
