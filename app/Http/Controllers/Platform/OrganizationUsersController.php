<?php

namespace App\Http\Controllers\Platform;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;
use App\Exceptions\Cert\InvalidResponseException;
use App\Repositories\OrganizationUserRepository;
use App\Repositories\UserRepository;
use App\Services\VatsimService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrganizationUsersController extends Controller
{
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

        $user = $this->userRepository->find($attributes['id']);
        if (!$user) {
            try {
                $user = $this->vatsimService->createUserFromCert($attributes['id']);
            } catch (InvalidResponseException $e) {
                throw ValidationException::withMessages([
                    'id' => ['Error retrieving user from VATSIM. Please check the CID and try again.'],
                ]);
            } catch (Exception $e) {
                throw ValidationException::withMessages([
                    'id' => ['Error retrieving user from VATSIM. Please try again later.'],
                ]);
            }
        }

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

        $this->entityManager->remove($this->organizationUserRepository->findByUserAndOrganization($user, $organization));
        $this->entityManager->flush();

        return redirect()->route('platform.organizations.edit', $organization)
            ->with('success', 'User deleted.');
    }
}
