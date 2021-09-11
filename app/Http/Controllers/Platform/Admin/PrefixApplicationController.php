<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Entities\OrganizationPrefixApplication;
use App\Events\PrefixApplicationApprovedEvent;
use App\Events\PrefixApplicationRejectedEvent;
use App\Repositories\PrefixApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;

class PrefixApplicationController extends Controller
{
    protected EntityManagerInterface $em;
    protected PrefixApplicationRepository $prefixApplicationRepository;

    public function __construct(EntityManagerInterface $entityManager, PrefixApplicationRepository $prefixApplicationRepository)
    {
        $this->middleware('platform');
        $this->middleware('admin');

        $this->em = $entityManager;
        $this->prefixApplicationRepository = $prefixApplicationRepository;
    }

    public function index()
    {
        $prefixApplications = $this->prefixApplicationRepository->findAll('id', 'asc', 20, Paginator::resolveCurrentPage());

        return view('platform.admin.prefix-applications.index')->with([
            'prefixApplications' => $prefixApplications,
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
        ]);
    }

    public function approve(Request $request, OrganizationPrefixApplication $prefixApplication)
    {
        $attributes = $this->validate($request, [
            'prefix' => 'required|alpha_num|max:50',
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
