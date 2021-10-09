<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\Cert\InvalidResponseException;
use Doctrine\ORM\EntityManagerInterface;

class VatsimService
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getUser(int $id)
    {
        $client = app('guzzle');
        $url = "https://api.vatsim.net/api/ratings/{$id}/";
        $res = $client->get($url);

        $user = json_decode($res->getBody(), true);

        $missingKeys = array_diff(['id'], array_keys($user));
        if (collect($missingKeys)->isNotEmpty()) {
            $missingKeys = implode(',', $missingKeys);
            throw new InvalidResponseException("Missing keys from {$url}: {$missingKeys}");
        }

        if ($user['id'] != $id) {
            throw new InvalidResponseException("User ID {$user['id']} does not match expected {$id}");
        }

        return $user;
    }

    /**
     * @param int $id
     * @return User
     * @throws InvalidResponseException
     */
    public function createUserFromCert(int $id)
    {
        $attributes = $this->getUser($id);

        $user = new User();
        $user->setId($attributes['id']);
        $user->setFirstName('#');
        $user->setLastName('#');
        $user->setVatsimStatusData($attributes);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
