<?php

namespace App\Http\Controllers\Platform;

use App\Entities\Domain;
use App\Entities\Organization;
use App\Entities\Url;
use App\Http\Controllers\Controller;
use App\Repositories\DomainRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Fluent;
use Illuminate\Validation\ValidationException;

class UrlController extends Controller
{
    protected EntityManagerInterface $entityManager;
    protected UrlRepository $urlRepository;
    protected DomainRepository $domainRepository;
    protected OrganizationRepository $organizationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlRepository $urlRepository,
        DomainRepository $domainRepository,
        OrganizationRepository $organizationRepository
    ) {
        $this->middleware('platform');
        $this->entityManager = $entityManager;
        $this->urlRepository = $urlRepository;
        $this->domainRepository = $domainRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function index(Request $request)
    {
        $attributes = $this->validate(
            $request,
            [
                'sort' => 'nullable|string|alpha_dash',
                'direction' => 'nullable|string|in:asc,desc',
            ]
        );

        $orderBy = $attributes['sort'] ?? 'fullUrl';
        $order = $attributes['direction'] ?? 'asc';

        $urls = $this->urlRepository
            ->findByUserOrTheirOrganizations($request->user(), $orderBy, $order, 20, Paginator::resolveCurrentPage());
        $publicUrls = $this->urlRepository->findPublic('fullUrl', 'asc', 20, Paginator::resolveCurrentPage());

        return view('platform.urls.index')->with(
            [
                'user' => $request->user(),
                'urls' => $urls,
                'publicUrls' => $publicUrls,
            ]
        );
    }

    public function create(Request $request)
    {
        $user = $request->user();

        $domains = $this->domainRepository->findPublicOrOwnedByUser($user, 'id', 'asc');

        $organizations = $user->getOrganizations();

        $prefixes = [];
        foreach ($organizations as $organization) {
            if ($prefix = $organization->getPrefix()) {
                $prefixes[] = $prefix;
            }
        }

        $url = new Url();
        $url->setUrl('');
        $url->setRedirectUrl('');

        return view('platform.urls.create')->with(
            [
                'domains' => $domains,
                'organizations' => $organizations,
                'prefixes' => $prefixes,
                'url' => $url,
                'newUrl' => true,
            ]
        );
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $attributes = $this->getValidationFactory()->make(
            $request->all(),
            [
                'domain_id' => 'required|integer|exists:'.\App\Entities\Domain::class.',id',
                'prefix' => 'nullable|string',
                'url' => [
                    'required',
                    'string',
                    'min:3',
                    'max:30',
                    'regex:/^[0-9a-zA-Z_-]+$/',
                    'not_in:about,contact,platform,support,abuse,info,terms-of-use,privacy-policy',
                ],
                'redirect_url' => 'required|url|max:1000',
                'organization_id' => 'nullable|integer|exists:'.Organization::class.',id',
            ],
            [
                'url.regex' => 'The url may only include alphanumeric characters, dashes and underscores.',
            ]
        )->sometimes(
            'url',
            'unique:'.Url::class.',url,NULL,id,domain,'.$request->input(
                'domain_id'
            ).',prefix,1,organization,'.$request->input('organization_id'),
            function (Fluent $input) {
                return (bool) $input->get('prefix');
            }
        )->sometimes(
            'url',
            'unique:'.Url::class.',url,NULL,id,domain,'.$request->input('domain_id').',prefix,0',
            function (Fluent $input) {
                return !(bool) $input->get('prefix');
            }
        )->validate();

        $this->validate(
            $request,
            [
                'url' => 'regex:/^[0-9a-zA-Z][0-9a-zA-Z_-]*[0-9a-zA-Z]$/',
            ],
            [
                'url.regex' => 'The url may not start or end with special characters.',
            ]
        );

        /** @var Domain $domain */
        $domain = $this->domainRepository->find($attributes['domain_id']);
        if (!$domain->isPublic()) {
            $validOrganizations = array_filter(
                $domain->getOrganizations(),
                function ($organization) use ($user) {
                    return array_search($organization, $user->getOrganizations()) !== false;
                }
            );
            if (empty($validOrganizations)) {
                throw new AuthorizationException();
            }
            $validOrganizationIds = array_map(fn ($organization) => $organization->getId(), $validOrganizations);
            if (!in_array($attributes['organization_id'], $validOrganizationIds)) {
                $validOrganizationNames = implode(
                    ', ',
                    array_map(
                        fn ($organization) => $organization->getName(),
                        $validOrganizations
                    )
                );
                throw ValidationException::withMessages(
                    [
                        'organization_id' => [
                            "The domain '{$domain->getUrl()}' can only be used with the following organizations: $validOrganizationNames",
                        ],
                    ]
                );
            }
        }

        if (!empty($attributes['prefix'])) {
            $organizationsWithPrefix = array_filter(
                $request->user()->getOrganizations(),
                function ($organization) use ($attributes) {
                    return $organization->getPrefix() === $attributes['prefix'];
                }
            );
            $organization = !empty($organizationsWithPrefix) ? $organizationsWithPrefix[0] : null;

            if (!$organization) {
                throw ValidationException::withMessages(
                    [
                        'prefix' => ['Prefix not found.'],
                    ]
                );
            } elseif ($organization->getId() != $attributes['organization_id']) {
                throw ValidationException::withMessages(
                    [
                        'organization_id' => [
                            "The '{$attributes['prefix']}' prefix can only be used with the {$organization->getName()} organization.",
                        ],
                    ]
                );
            }

            $attributes['prefix'] = true;
        } else {
            $attributes['prefix'] = false;
        }

        $url = new Url();
        $url->setUrl($attributes['url']);
        $url->setRedirectUrl($attributes['redirect_url']);
        $url->setDomain($domain);
        if ($attributes['organization_id'] !== null) {
            $url->setOrganization(
                $this->entityManager->getReference(Organization::class, $attributes['organization_id'])
            );
        } else {
            $url->setUser($request->user());
        }
        $this->authorize('create', $url);
        $this->entityManager->persist($url);
        $this->entityManager->flush();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL created.');
    }

    public function show(Url $url)
    {
        Session::reflash();

        return redirect()->route('platform.urls.edit', $url);
    }

    public function edit(Request $request, Url $url)
    {
        $this->authorize('update', $url);

        return view('platform.urls.edit')->with(
            [
                'organizations' => $request->user()->getOrganizations(),
                'url' => $url,
                'newUrl' => false,
            ]
        );
    }

    public function update(Request $request, Url $url)
    {
        $this->authorize('update', $url);

        $attributes = $this->validate(
            $request,
            [
                'redirect_url' => 'required|url|max:1000',
                'organization_id' => 'nullable|integer|exists:'.Organization::class.',id',
            ]
        );

        $oldOrganizationId = $url->getOrganization() ? $url->getOrganization()->getId() : null;
        if ($attributes['organization_id'] !== $oldOrganizationId) {
            $this->authorize('move', $url);

            if ($attributes['organization_id'] !== null) {
                $this->authorize('act-as-member', $this->organizationRepository->find($attributes['organization_id']));
            }
        }

        $url->setRedirectUrl($attributes['redirect_url']);
        if ($attributes['organization_id'] !== null) {
            $url->setOrganization(
                $this->entityManager->getReference(Organization::class, $attributes['organization_id'])
            );
            $url->setUser(null);
        } else {
            $url->setUser($request->user());
            $url->setOrganization(null);
        }

        $this->entityManager->flush();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL updated.');
    }

    public function destroy(Url $url)
    {
        $this->authorize('delete', $url);

        $this->entityManager->remove($url);
        $this->entityManager->flush();

        return redirect()->route('platform.urls.index')
            ->with('success', 'URL deleted.');
    }
}
