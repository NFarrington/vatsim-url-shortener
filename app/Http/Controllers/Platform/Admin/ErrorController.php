<?php

namespace App\Http\Controllers\Platform\Admin;

class ErrorController extends Controller
{
    public function __construct()
    {
        $this->middleware('platform');
        $this->middleware('admin');
    }

    public function generateError($statusCode = 500)
    {
        abort($statusCode);
    }
}
