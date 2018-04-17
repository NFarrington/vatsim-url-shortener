<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UrlController extends Controller
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
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $urls = Url::where('user_id', $request->user()->id)
            ->join('domains', 'urls.domain_id', 'domains.id')
            ->orderBy('domains.url')
            ->orderBy('urls.url')
            ->paginate(20, ['urls.*']);
        $publicUrls = Url::public()
            ->join('domains', 'urls.domain_id', 'domains.id')
            ->orderBy('domains.url')
            ->orderBy('urls.url')
            ->get(['urls.*']);

        return view('platform.urls.index')->with([
            'urls' => $urls,
            'publicUrls' => $publicUrls,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('platform.urls.create')->with([
            'domains' => Domain::orderBy('id')->get(),
            'url' => new Url(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attributes = $this->validate($request, [
            'domain_id' => 'required|integer|exists:domains,id',
            'url' => [
                'required',
                'string',
                'min:3',
                'max:250',
                'regex:/^[0-9a-zA-Z_-]+$/',
                'not_in:about,contact,platform,support,abuse,info',
                Rule::unique('urls')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'redirect_url' => 'required|url|max:1000',
        ], [
            'url.regex' => 'The url may only include alphanumeric characters, dashes and underscores.',
        ]);

        $this->validate($request, [
            'url' => 'regex:/^[0-9a-zA-Z][0-9a-zA-Z_-]*[0-9a-zA-Z]$/',
        ], [
            'url.regex' => 'The url may not start or end with special characters.',
        ]);

        $url = new Url($attributes);
        $url->user_id = $request->user()->id;
        $url->save();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL created.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Url $url
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Url $url)
    {
        $this->authorize('delete', $url);

        $url->delete();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL deleted.');
    }
}
