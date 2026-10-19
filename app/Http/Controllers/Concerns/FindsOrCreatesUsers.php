<?php

namespace App\Http\Controllers\Concerns;

use App\Entities\User;
use App\Exceptions\Cert\InvalidResponseException;
use App\Repositories\UserRepository;
use App\Services\VatsimService;
use Exception;
use Illuminate\Validation\ValidationException;

trait FindsOrCreatesUsers
{
    /**
     * Find a user by their VATSIM CID, creating them from VATSIM's records if they haven't registered.
     *
     * @throws ValidationException
     */
    protected function findOrCreateUser(int $id, UserRepository $userRepository, VatsimService $vatsimService): User
    {
        $user = $userRepository->find($id);
        if ($user) {
            return $user;
        }

        try {
            return $vatsimService->createUserFromCert($id);
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
}
