<?php

namespace App\Http\Controllers\Platform;

use App\Models\Organization;
use App\Models\OrganizationUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OrganizationController extends Controller
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
        $organizations = Organization::whereHas('users', function ($query) use ($request) {
            $query->where('users.id', $request->user()->id);
        })->orderBy('created_at')->paginate(20);

        return view('platform.organizations.index')->with([
            'organizations' => $organizations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('platform.organizations.create')->with([
            'organization' => new Organization(),
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
            'name' => 'required|string|min:3|max:50',
        ]);

        $organization = Organization::create($attributes);
        $organization->users()->attach(
            $request->user()->id, ['role_id' => OrganizationUser::ROLE_MANAGER]
        );

        return redirect()->route('platform.organizations.index')
            ->with('success', 'Organization created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Organization $organization
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization)
    {
        Session::reflash();

        return redirect()->route('platform.organizations.edit', $organization);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Organization $organization
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Organization $organization)
    {
        $this->authorize('update', $organization);

        return view('platform.organizations.edit')->with([
            'organization' => $organization,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Organization $organization
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, Organization $organization)
    {
        $this->authorize('update', $organization);

        $attributes = $this->validate($request, [
            'name' => 'required|string|min:3|max:50',
        ]);

        $organization->update($attributes);

        return redirect()->route('platform.organizations.index')
            ->with('success', 'Organization updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Organization $organization
     * @return \Illuminate\Http\Response
     * @throws \Exception|\Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Organization $organization)
    {
        $this->authorize('delete', $organization);

        if ($organization->urls->isNotEmpty()) {
            return redirect()->route('platform.organizations.index')
                ->with('error', 'This organization has URLs associated with it.');
        }

        $organization->users()->get()->each(function ($user) {
            $user->pivot->update(['deleted_at' => Carbon::now()]);
        });
        $organization->delete();

        return redirect()->route('platform.organizations.index')
            ->with('success', 'Organization deleted.');
    }
}
