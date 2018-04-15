<?php

namespace App\Http\Controllers\Platform;

use App\Models\News;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('platform');
    }

    /**
     * Display the dashboard page.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        $news = News::published()->orderByDesc('created_at')->paginate(5);

        return view('platform.dashboard')->with([
            'news' => $news,
        ]);
    }
}
