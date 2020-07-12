<?php

namespace App\Http\Controllers\Platform;

class InfoController extends Controller
{
    public function support()
    {
        return view('platform.support');
    }

    public function terms()
    {
        return view('platform.terms-of-use');
    }

    public function privacy()
    {
        return view('platform.privacy-policy');
    }
}
