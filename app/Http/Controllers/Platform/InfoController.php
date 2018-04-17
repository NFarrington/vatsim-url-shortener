<?php

namespace App\Http\Controllers\Platform;

class InfoController extends Controller
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

    /**
     * Display the terms of use page.
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view('platform.terms-of-use');
    }

    /**
     * Display the privacy policy page.
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('platform.privacy-policy');
    }
}
