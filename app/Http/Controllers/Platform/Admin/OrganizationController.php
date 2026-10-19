<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Entities\Organization;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationUserRepository;
use Illuminate\Pagination\Paginator;

class OrganizationController extends Controller
{
    protected OrganizationRepository $organizationRepository;
    protected OrganizationUserRepository $organizationUserRepository;

    public function __construct(
        OrganizationRepository $organizationRepository,
        OrganizationUserRepository $organizationUserRepository
    ) {
        $this->middleware('platform');
        $this->middleware('admin');

        $this->organizationRepository = $organizationRepository;
        $this->organizationUserRepository = $organizationUserRepository;
    }

    public function index()
    {
        $organizations = $this->organizationRepository->findAllOrdered('id', 'asc', 100, Paginator::resolveCurrentPage());

        return view('platform.admin.organizations.index')->with([
            'organizations' => $organizations,
            'memberCounts' => $this->organizationUserRepository->countByOrganizations($organizations->items()),
        ]);
    }

    public function show(Organization $organization)
    {
        return view('platform.admin.organizations.show')->with([
            'organization' => $organization,
            'organizationUsers' => $this->organizationUserRepository->findByOrganizationWithUsers($organization),
        ]);
    }
}
