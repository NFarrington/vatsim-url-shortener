<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DomainController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('platform');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $domains = Domain::orderBy('id')->paginate(20);

        return view('platform.admin.domains.index')->with([
            'domains' => $domains,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('platform.admin.domains.create')->with([
            'domain' => new Domain(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attributes = $this->validate($request, [
            'url' => 'required|string|max:250',
        ]);

        Domain::create($attributes);

        return redirect()->route('platform.admin.domains.index')
            ->with('success', 'Domain created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function show(Domain $domain)
    {
        Session::reflash();

        return redirect()->route('platform.admin.domains.edit', $domain);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function edit(Domain $domain)
    {
        return view('platform.admin.domains.edit')->with([
            'domain' => $domain,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Domain  $domain
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Domain $domain)
    {
        $attributes = $this->validate($request, [
            'url' => 'required|string|max:250',
        ]);

        $domain->update($attributes);

        return redirect()->route('platform.admin.domains.index')
            ->with('success', 'Domain updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Domain $domain
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Domain $domain)
    {
        if ($domain->urls->isNotEmpty()) {
            return redirect()->back()
                ->with('error', 'There are currently URLs associated with this domain.');
        }

        $domain->delete();

        return redirect()->route('platform.admin.domains.index')
            ->with('success', 'Domain deleted.');
    }
}
