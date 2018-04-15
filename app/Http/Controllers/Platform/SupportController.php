<?php

namespace App\Http\Controllers\Platform;

class SupportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Show the support page.
     *
     * @return \Illuminate\View\View
     */
    public function support()
    {
        return view('platform.support');
    }
}
