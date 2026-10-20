<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Entities\OrganizationPrefixApplication;
use App\Events\PrefixApplicationApprovedEvent;
use App\Events\PrefixApplicationRejectedEvent;
use App\Repositories\OrganizationRepository;
use App\Repositories\PrefixApplicationRepository;
use App\Rules\AvailablePrefix;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;

class PrefixApplicationController extends Controller
{
    protected EntityManagerInterface $em;
    protected PrefixApplicationRepository $prefixApplicationRepository;
    protected OrganizationRepository $organizationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PrefixApplicationRepository $prefixApplicationRepository,
        OrganizationRepository $organizationRepository
    ) {
        $this->middleware('platform');
        $this->middleware('admin');

        $this->em = $entityManager;
        $this->prefixApplicationRepository = $prefixApplicationRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function index()
    {
        $prefixApplications = $this->prefixApplicationRepository->findAllOrdered('id', 'asc', 20, Paginator::resolveCurrentPage());

        $prefixOwners = $this->organizationRepository->findByPrefixesIgnoringCase(
            array_map(fn ($application) => $application->getPrefix(), $prefixApplications->items())
        );

        return view('platform.admin.prefix-applications.index')->with([
            'prefixApplications' => $prefixApplications,
            'prefixOwners' => $prefixOwners,
        ]);
    }

    public function show(OrganizationPrefixApplication $prefixApplication)
    {
        Session::reflash();

        return redirect()->route('platform.admin.prefix-applications.edit', $prefixApplication);
    }

    public function edit(OrganizationPrefixApplication $prefixApplication)
    {
        return view('platform.admin.prefix-applications.review')->with([
            'prefixApplication' => $prefixApplication,
            'prefixOwner' => $this->organizationRepository->findOneByPrefixIgnoringCase($prefixApplication->getPrefix()),
        ]);
    }

    public function approve(Request $request, OrganizationPrefixApplication $prefixApplication)
    {
        $attributes = $this->validate($request, [
            'prefix' => ['required', 'alpha_num', 'max:50', new AvailablePrefix()],
        ]);

        $organization = $prefixApplication->getOrganization();
        $organization->setPrefix($attributes['prefix']);
        $this->em->remove($prefixApplication);
        $this->em->flush();

        event(new PrefixApplicationApprovedEvent($prefixApplication, $attributes['prefix']));

        return redirect()->route('platform.admin.prefix-applications.index')
            ->with('success', 'Prefix application approved.');
    }

    public function reject(Request $request, OrganizationPrefixApplication $prefixApplication)
    {
        $attributes = $this->validate($request, [
            'reason' => 'required|string',
        ]);

        $this->em->remove($prefixApplication);
        $this->em->flush();

        event(new PrefixApplicationRejectedEvent($prefixApplication, $attributes['reason']));

        return redirect()->route('platform.admin.prefix-applications.index')
            ->with('success', 'Prefix application rejected.');
    }
}
