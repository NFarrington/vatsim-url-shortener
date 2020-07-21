<?php

namespace App\Http\Controllers\Platform;

use App\Entities\Organization;
use App\Entities\OrganizationPrefixApplication;
use App\Events\PrefixApplicationCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;

class OrganizationPrefixController extends Controller
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->middleware('platform');
        $this->entityManager = $entityManager;
    }

    public function create(Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        if ($organization->getPrefixApplication()) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix application pending approval.');
        } elseif ($organization->getPrefix()) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix.');
        }

        $application = new OrganizationPrefixApplication();
        $application->setOrganization($organization);
        $application->setIdentityUrl('');
        $application->setPrefix('');

        return view('platform.organizations.prefix.create')->with([
            'prefixApplication' => $application,
        ]);
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        if ($organization->getPrefixApplication()) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix application pending approval.');
        } elseif ($organization->getPrefix()) {
            return redirect()->route('platform.organizations.show', $organization)
                ->with('error', 'Your organization already has a prefix.');
        }

        $attributes = $this->validate($request, [
            'identity_url' => 'required|url|max:1000',
            'prefix' => 'required|alpha_num|max:50',
        ]);

        $application = new OrganizationPrefixApplication();
        $application->setIdentityUrl($attributes['identity_url']);
        $application->setPrefix($attributes['prefix']);
        $application->setOrganization($organization);
        $application->setUser($request->user());
        $this->entityManager->persist($application);
        $this->entityManager->flush();

        event(new PrefixApplicationCreatedEvent($application));

        return redirect()->route('platform.organizations.show', $organization)
            ->with('success', 'Prefix application submitted.');
    }
}
