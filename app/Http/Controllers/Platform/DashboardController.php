<?php

namespace App\Http\Controllers\Platform;

use App\Repositories\NewsRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    protected NewsRepository $newsRepository;

    public function __construct(NewsRepository $newsRepository)
    {
        $this->middleware('platform');
        $this->newsRepository = $newsRepository;
    }

    public function platform()
    {
        Session::reflash();

        return redirect()->route('platform.dashboard');
    }

    public function dashboard()
    {
        $news = $this->newsRepository->findPublished('createdAt', 'desc', 5, Paginator::resolveCurrentPage());

        return view('platform.dashboard')->with([
            'news' => $news,
        ]);
    }
}
