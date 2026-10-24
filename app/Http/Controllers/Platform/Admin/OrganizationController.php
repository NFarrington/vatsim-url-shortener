<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Entities\Organization;
use App\Http\Controllers\Concerns\ReadsListingOrder;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationUserRepository;
use App\Repositories\UrlRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class OrganizationController extends Controller
{
    use ReadsListingOrder;

    protected OrganizationRepository $organizationRepository;
    protected OrganizationUserRepository $organizationUserRepository;
    protected UrlRepository $urlRepository;

    public function __construct(
        OrganizationRepository $organizationRepository,
        OrganizationUserRepository $organizationUserRepository,
        UrlRepository $urlRepository
    ) {
        $this->middleware('platform');
        $this->middleware('admin');

        $this->organizationRepository = $organizationRepository;
        $this->organizationUserRepository = $organizationUserRepository;
        $this->urlRepository = $urlRepository;
    }

    public function index(Request $request)
    {
        // Sorting by ID isn't offered in the view, but it's the default.
        [$orderBy, $order] = $this->listingOrder($request, ['prefix'], 'id');

        $organizations = $this->organizationRepository->findAllOrdered($orderBy, $order, 100, Paginator::resolveCurrentPage());

        return view('platform.admin.organizations.index')->with([
            'organizations' => $organizations,
            'memberCounts' => $this->organizationUserRepository->countByOrganizations($organizations->items()),
            'urlCounts' => $this->urlRepository->countByOrganizations($organizations->items()),
        ]);
    }

    public function show(Organization $organization)
    {
        return view('platform.admin.organizations.show')->with([
            'organization' => $organization,
            'organizationUsers' => $this->organizationUserRepository->findByOrganizationWithUsers($organization),
            'urls' => $this->urlRepository->findByOrganization($organization, 100, Paginator::resolveCurrentPage()),
        ]);
    }
}
