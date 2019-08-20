<?php

namespace App\Http\Controllers;

use App\Models\Organization;
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
     * @param string|null $prefix
     * @param string|null $shortUrl
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request, $prefix = null, $shortUrl = null)
    {
        if ($prefix && !$shortUrl) {
            $shortUrl = $prefix;
            $prefix = null;
        }

        $url = Url::where('url', $shortUrl ?: '/')
            ->whereHas('domain', function ($query) use ($request) {
                $query->where('url', $request->root().'/');
            });

        if ($prefix) {
            $organization = Organization::where('prefix', $prefix)->first();
            if (!$organization) {
                throw new NotFoundHttpException();
            }
            $url = $url->where('organization_id', $organization->id);
        }

        $url = $url->first();

        if (!$url) {
            throw new NotFoundHttpException();
        }

        request()->session()->flash('short.url', $url);

        return redirect()->to($url->redirect_url);
    }
}
