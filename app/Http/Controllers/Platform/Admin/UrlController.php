<?php

namespace App\Http\Controllers\Platform\Admin;

use App\Entities\Organization;
use App\Entities\Url;
use App\Http\Controllers\Concerns\FindsOrCreatesUsers;
use App\Http\Controllers\Concerns\ReadsListingOrder;
use App\Repositories\OrganizationRepository;
use App\Repositories\UrlRepository;
use App\Repositories\UserRepository;
use App\Services\UrlService;
use App\Services\UrlStoreService;
use App\Services\VatsimService;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class UrlController extends Controller
{
    use FindsOrCreatesUsers, ReadsListingOrder;

    const OWNER_NONE = 'none';
    const OWNER_USER = 'user';
    const OWNER_ORGANIZATION = 'organization';

    protected EntityManagerInterface $em;
    protected UrlRepository $urlRepository;
    protected OrganizationRepository $organizationRepository;
    protected UserRepository $userRepository;
    protected VatsimService $vatsimService;
    protected UrlService $urlService;
    protected UrlStoreService $urlStoreService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UrlRepository $urlRepository,
        OrganizationRepository $organizationRepository,
        UserRepository $userRepository,
        VatsimService $vatsimService,
        UrlService $urlService,
        UrlStoreService $urlStoreService
    ) {
        $this->middleware('platform');
        $this->middleware('admin');

        $this->em = $entityManager;
        $this->urlRepository = $urlRepository;
        $this->organizationRepository = $organizationRepository;
        $this->userRepository = $userRepository;
        $this->vatsimService = $vatsimService;
        $this->urlService = $urlService;
        $this->urlStoreService = $urlStoreService;
    }

    public function index(Request $request)
    {
        [$orderBy, $order] = $this->listingOrder(
            $request,
            ['id', 'fullUrl', 'redirectUrl', 'owner', 'updatedAt'],
            'id'
        );
        $search = is_string($request->query('q')) ? trim($request->query('q')) : null;

        $urls = $this->urlRepository->search($search, $orderBy, $order, 100, Paginator::resolveCurrentPage());

        return view('platform.admin.urls.index')->with([
            'urls' => $urls,
            'search' => $search,
        ]);
    }

    public function show(Url $url)
    {
        Session::reflash();

        return redirect()->route('platform.admin.urls.edit', $url);
    }

    public function edit(Url $url)
    {
        return view('platform.admin.urls.edit')->with([
            'url' => $url,
            'ownerType' => $this->ownerType($url),
            'organizations' => $this->organizationRepository->findAllOrdered('name', 'asc'),
        ]);
    }

    public function update(Request $request, Url $url)
    {
        $attributes = $this->validate($request, [
            'redirect_url' => 'required|url:http,https|max:1000',
            'owner_type' => ['required', Rule::in([self::OWNER_NONE, self::OWNER_USER, self::OWNER_ORGANIZATION])],
            'user_id' => 'nullable|required_if:owner_type,'.self::OWNER_USER.'|integer|min:1',
            'organization_id' => 'nullable|required_if:owner_type,'.self::OWNER_ORGANIZATION.'|integer|exists:'.Organization::class.',id',
        ], [
            'user_id.required_if' => 'Enter the CID of the user to transfer the URL to.',
            'organization_id.required_if' => 'Choose the organization to transfer the URL to.',
        ]);

        $ownerType = $attributes['owner_type'];
        $userId = $ownerType === self::OWNER_USER ? (int) $attributes['user_id'] : null;
        $organizationId = $ownerType === self::OWNER_ORGANIZATION ? (int) $attributes['organization_id'] : null;

        $ownerChanged = $ownerType !== $this->ownerType($url)
            || ($ownerType === self::OWNER_USER && $userId !== $url->getUser()->getId())
            || ($ownerType === self::OWNER_ORGANIZATION && $organizationId !== $url->getOrganization()->getId());

        if ($ownerChanged) {
            $this->authorize('transfer', $url);

            $url->setUser(
                $userId !== null
                    ? $this->findOrCreateUser($userId, $this->userRepository, $this->vatsimService, 'user_id')
                    : null
            );
            $url->setOrganization(
                $organizationId !== null
                    ? $this->em->getReference(Organization::class, $organizationId)
                    : null
            );
        }

        $url->setRedirectUrl($attributes['redirect_url']);

        $this->em->flush();
        $this->urlStoreService->createOrUpdate($url);

        return redirect()->route('platform.admin.urls.index')
            ->with('success', 'URL updated.');
    }

    public function destroy(Url $url)
    {
        $this->em->remove($url);
        $this->em->flush();
        $this->urlStoreService->delete($url);
        $this->urlService->removeUrlFromCache($url);

        return redirect()->route('platform.admin.urls.index')
            ->with('success', 'URL deleted.');
    }

    /**
     * The kind of owner the URL has. URLs with an organization belong to it, even if they also have a user.
     */
    protected function ownerType(Url $url): string
    {
        if ($url->getOrganization()) {
            return self::OWNER_ORGANIZATION;
        }

        return $url->getUser() ? self::OWNER_USER : self::OWNER_NONE;
    }
}
