<?php

namespace App\Http\Controllers\Platform;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Repositories\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OrganizationController extends Controller
{
    protected OrganizationRepository $organizationRepository;
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager, OrganizationRepository $organizationRepository)
    {
        $this->middleware('platform');
        $this->organizationRepository = $organizationRepository;
        $this->em = $entityManager;
    }

    public function index(Request $request)
    {
        $attributes = $this->validate($request, [
            'sort' => 'nullable|string|alpha_dash',
            'direction' => 'nullable|string|in:asc,desc',
        ]);

        $orderBy = $attributes['sort'] ?? 'name';
        $order = $attributes['direction'] ?? 'asc';

        $organizations = $this->organizationRepository->findByUser($request->user(), $orderBy, $order);

        return view('platform.organizations.index')->with([
            'organizations' => $organizations,
        ]);
    }

    public function create()
    {
        $organization = new Organization();
        $organization->setName('');

        return view('platform.organizations.create')->with([
            'organization' => $organization,
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $this->validate($request, [
            'name' => 'required|string|min:3|max:50',
        ]);

        $organization = new Organization();
        $organization->setName($attributes['name']);

        $organizationUser = new OrganizationUser();
        $organizationUser->setOrganization($organization);
        $organizationUser->setUser($request->user());
        $organizationUser->setRoleId(OrganizationUser::ROLE_OWNER);
        $this->em->persist($organization);
        $this->em->persist($organizationUser);
        $this->em->flush();

        return redirect()->route('platform.organizations.index')
            ->with('success', 'Organization created.');
    }

    public function show(Organization $organization)
    {
        Session::reflash();

        return redirect()->route('platform.organizations.edit', $organization);
    }

    public function edit(Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        return view('platform.organizations.edit')->with([
            'organization' => $organization,
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        $attributes = $this->validate($request, [
            'name' => 'required|string|min:3|max:50',
        ]);

        $organization->setName($attributes['name']);
        $this->em->flush();

        return redirect()->route('platform.organizations.index')
            ->with('success', 'Organization updated.');
    }

    public function destroy(Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        if (!empty($organization->getUrls())) {
            return redirect()->route('platform.organizations.index')
                ->with('error', 'This organization has URLs associated with it.');
        }

        $this->em->remove($organization);
        $this->em->flush();

        return redirect()->route('platform.organizations.index')
            ->with('success', 'Organization deleted.');
    }
}
