<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('log-requests');
    }

    /**
     * Redirect a short URL to the intended URL.
     *
     * @param \Illuminate\Http\Request $request
     * @param $shortUrl
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request, $shortUrl = null)
    {
        $url = Url::where('url', $shortUrl ?: '/')
            ->whereHas('domain', function ($query) use ($request) {
                $query->where('url', $request->root().'/');
            })->first();

        if (!$url) {
            throw new NotFoundHttpException();
        }

        request()->session()->flash('short.url_id', $url->id);

        return redirect()->to($url->redirect_url);
    }
}
