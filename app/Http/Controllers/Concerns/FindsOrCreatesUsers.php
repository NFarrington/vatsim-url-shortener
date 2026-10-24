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
     * @param  string  $attribute  the input the CID came from, for validation errors
     *
     * @throws ValidationException
     */
    protected function findOrCreateUser(
        int $id,
        UserRepository $userRepository,
        VatsimService $vatsimService,
        string $attribute = 'id'
    ): User {
        $user = $userRepository->find($id);
        if ($user) {
            return $user;
        }

        try {
            return $vatsimService->createUserFromCert($id);
        } catch (InvalidResponseException $e) {
            throw ValidationException::withMessages([
                $attribute => ['Error retrieving user from VATSIM. Please check the CID and try again.'],
            ]);
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                $attribute => ['Error retrieving user from VATSIM. Please try again later.'],
            ]);
        }
    }
}
