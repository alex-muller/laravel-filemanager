<?php

namespace AlexMuller\Filemanager\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{

    public function __construct()
    {
        $this->middleware(config('amfm.middleware'));
    }

    public function index()
    {
        return view('amfm::index');
    }
}
