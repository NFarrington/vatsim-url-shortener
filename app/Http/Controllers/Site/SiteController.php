<?php

namespace App\Http\Controllers\Site;

class SiteController extends Controller
{
    /**
     * Display the index page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('site.index');
    }

    /**
     * Display the about page.
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('site.about');
    }

    /**
     * Display the contact page.
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('site.contact');
    }
}
