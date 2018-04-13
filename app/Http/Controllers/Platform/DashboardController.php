<?php

namespace App\Http\Controllers\Platform;

class DashboardController extends Controller
{
    /**
     * Display the dashboard page.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        return view('platform.dashboard');
    }
}
