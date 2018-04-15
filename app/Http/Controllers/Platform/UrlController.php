<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
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
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $urls = Url::where('user_id', $request->user()->id)
            ->orderBy('url')
            ->paginate(20);

        return view('platform.urls.index')->with([
            'urls' => $urls,
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
            'url' => [
                'required',
                'string',
                'max:250',
                Rule::unique('urls')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'redirect_url' => 'required|url|max:1000',
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
