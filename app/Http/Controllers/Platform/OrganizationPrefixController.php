<?php

namespace App\Http\Controllers\Platform;

use App\Models\Organization;
use App\Models\OrganizationPrefixApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OrganizationPrefixController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @param \App\Models\Organization $organization
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        if ($organization->prefixApplication) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix application pending approval.');
        } elseif ($organization->prefix) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix.');
        }

        return view('platform.organizations.prefix.create')->with([
            'organization' => $organization,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param \App\Models\Organization $organization
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        if ($organization->prefixApplication) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix application pending approval.');
        } elseif ($organization->prefix) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix.');
        }

        $attributes = $this->validate($request, [
            'identity_url' => 'required|url|max:1000',
            'prefix' => 'required|alpha_num|max:50',
        ]);

        $application = new OrganizationPrefixApplication($attributes);
        $application->organization_id = $organization->id;
        $application->user_id = $request->user()->id;
        $application->save();

        return redirect()->route('platform.organizations.show', $organization)
            ->with('success', 'Prefix application submitted.');
    }
}
