<?php

namespace App\Http\Controllers;

use App\Models\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UrlController extends Controller
{
    /**
     * Redirect a short URL to the intended URL.
     *
     * @param $shortUrl
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($shortUrl)
    {
        $url = Url::where('url', $shortUrl)->first();

        if (!$url) {
            throw new NotFoundHttpException();
        }

        return redirect()->to($url->redirect_url);
    }
}
