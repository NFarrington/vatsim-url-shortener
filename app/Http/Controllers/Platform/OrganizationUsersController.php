<?php

namespace App\Http\Controllers\Platform;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;
use App\Http\Controllers\Concerns\FindsOrCreatesUsers;
use App\Repositories\OrganizationUserRepository;
use App\Repositories\UserRepository;
use App\Services\VatsimService;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationUsersController extends Controller
{
    use FindsOrCreatesUsers;

    protected EntityManagerInterface $entityManager;
    protected UserRepository $userRepository;
    protected VatsimService $vatsimService;
    protected OrganizationUserRepository $organizationUserRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository, VatsimService $vatsimService, OrganizationUserRepository $organizationUserRepository)
    {
        $this->middleware('platform');
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->vatsimService = $vatsimService;
        $this->organizationUserRepository = $organizationUserRepository;
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorize('act-as-owner', $organization);

        $existingUserIds = array_map(fn ($user) => $user->getId(), $organization->getUsers());
        $attributes = $this->validate($request, [
            'id' => [
                'required',
                'integer',
                Rule::notIn($existingUserIds),
            ],
            'role_id' => [
                'required',
                'integer',
                Rule::in([
                    OrganizationUser::ROLE_OWNER,
                    OrganizationUser::ROLE_MANAGER,
                    OrganizationUser::ROLE_MEMBER,
                ]),
            ],
        ], [
            'id.not_in' => 'That user is already in this organization.',
        ]);

        $user = $this->findOrCreateUser($attributes['id'], $this->userRepository, $this->vatsimService);

        $organizationUser = new OrganizationUser();
        $organizationUser->setOrganization($organization);
        $organizationUser->setUser($user);
        $organizationUser->setRoleId($attributes['role_id']);
        $this->entityManager->persist($organizationUser);
        $this->entityManager->flush();

        return redirect()->route('platform.organizations.edit', $organization)
            ->with('success', 'User added.');
    }

    public function destroy(Request $request, Organization $organization, User $user)
    {
        $this->authorize('act-as-owner', $organization);

        if ($request->user()->getId() == $user->getId()) {
            return redirect()->route('platform.organizations.edit', $organization)
                ->with('error', 'You cannot remove yourself.');
        }

        $organizationUser = $this->organizationUserRepository->findByUserAndOrganization($user, $organization);
        if (!$organizationUser) {
            return redirect()->route('platform.organizations.edit', $organization)
                ->with('error', 'That user is not in this organization.');
        }

        $this->entityManager->remove($organizationUser);
        $this->entityManager->flush();

        return redirect()->route('platform.organizations.edit', $organization)
            ->with('success', 'User deleted.');
    }
}
