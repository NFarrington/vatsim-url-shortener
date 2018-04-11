<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('site.index');
    }

    public function about()
    {
        return view('site.about');
    }

    public function contact()
    {
        return view('site.contact');
    }

    /**
     * Display the index page.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        return view('welcome');
    }
}
