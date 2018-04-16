<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlController extends Controller
{
    /**
     * Redirect a short URL to the intended URL.
     *
     * @param \Illuminate\Http\Request $request
     * @param $shortUrl
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request, $shortUrl)
    {
        $url = Url::where('url', $shortUrl)
            ->whereHas('domain', function ($query) use ($request) {
                $query->where('url', $request->root().'/');
            })->first();

        if (!$url) {
            throw new NotFoundHttpException();
        }

        return redirect()->to($url->redirect_url);
    }
}
