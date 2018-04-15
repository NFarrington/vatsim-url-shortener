<?php

namespace App\Http\Controllers\Platform\Admin;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('platform');
        $this->middleware('admin');
    }

    /**
     * Display the admin page.
     */
    public function admin()
    {
        session()->reflash();

        return redirect()
            ->route('platform.admin.news.index');
    }
}
