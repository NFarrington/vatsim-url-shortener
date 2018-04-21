<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Organization;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
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
            ->orWhereIn('organization_id', $request->user()->organizations()->pluck('organizations.id'))
            ->join('domains', 'urls.domain_id', 'domains.id')
            ->orderBy('organization_id')
            ->orderBy('domains.url')
            ->orderBy('urls.url')
            ->paginate(20, ['urls.*']);
        $publicUrls = Url::public()
            ->join('domains', 'urls.domain_id', 'domains.id')
            ->orderBy('domains.url')
            ->orderBy('urls.url')
            ->get(['urls.*']);

        return view('platform.urls.index')->with([
            'user' => $request->user(),
            'urls' => $urls,
            'publicUrls' => $publicUrls,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('platform.urls.create')->with([
            'domains' => Domain::orderBy('id')->get(),
            'organizations' => $request->user()->organizations,
            'url' => new Url(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $attributes = $this->validate($request, [
            'domain_id' => 'required|integer|exists:domains,id',
            'url' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[0-9a-zA-Z_-]+$/',
                'not_in:about,contact,platform,support,abuse,info,terms-of-use,privacy-policy',
                Rule::unique('urls')->where(function ($query) use ($request) {
                    return $query->where('domain_id', $request->input('domain_id'))
                        ->whereNull('deleted_at');
                }),
            ],
            'redirect_url' => 'required|url|max:1000',
            'organization_id' => 'nullable|integer|exists:organizations,id',
        ], [
            'url.regex' => 'The url may only include alphanumeric characters, dashes and underscores.',
        ]);

        $this->validate($request, [
            'url' => 'regex:/^[0-9a-zA-Z][0-9a-zA-Z_-]*[0-9a-zA-Z]$/',
        ], [
            'url.regex' => 'The url may not start or end with special characters.',
        ]);

        if ($attributes['organization_id']) {
            $this->authorize('view', Organization::find($attributes['organization_id']));
        }

        $url = new Url($attributes);
        if ($attributes['organization_id'] === null) {
            $url->user_id = $request->user()->id;
        }
        $url->save();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL created.');
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Url $url
     * @return \Illuminate\Http\Response
     */
    public function show(Url $url)
    {
        Session::reflash();

        return redirect()->route('platform.urls.edit', $url);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Url $url
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Url $url)
    {
        return view('platform.urls.edit')->with([
            'domains' => Domain::orderBy('id')->get(),
            'organizations' => $request->user()->organizations,
            'url' => $url,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\Models\Url $url
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Url $url)
    {
        $this->authorize('update', $url);

        $attributes = $this->validate($request, [
            'redirect_url' => 'required|url|max:1000',
            'organization_id' => 'nullable|integer|exists:organizations,id',
        ]);

        if ($attributes['organization_id']) {
            $this->authorize('view', Organization::find($attributes['organization_id']));
        }

        $url->fill($attributes);
        $url->user_id = $attributes['organization_id'] === null
            ? $request->user()->id
            : null;
        $url->save();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Url $url
     * @return \Illuminate\Http\Response
     * @throws \Exception|\Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Url $url)
    {
        $this->authorize('delete', $url);

        $url->delete();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL deleted.');
    }
}
