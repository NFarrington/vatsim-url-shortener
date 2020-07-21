<?php

namespace App\Services;

use App\Entities\User;
use App\Exceptions\Cert\InvalidResponseException;
use Doctrine\ORM\EntityManagerInterface;
use SimpleXMLElement;

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
        $url = "https://cert.vatsim.net/vatsimnet/idstatusint.php?cid={$id}";
        $res = $client->get($url);

        $data = json_decode(json_encode(new SimpleXMLElement($res->getBody())), true);
        $user = $data['user'];
        $user['id'] = $user['@attributes']['cid'];
        unset($user['@attributes']);

        if ($user['id'] != $id) {
            throw new InvalidResponseException("User ID {$user['id']} does not match expected {$id}");
        }

        $missingKeys = array_diff(['id', 'name_first', 'name_last'], array_keys($user));
        if (collect($missingKeys)->isNotEmpty()) {
            $missingKeys = implode(',', $missingKeys);
            throw new InvalidResponseException("Missing keys from {$url}: {$missingKeys}");
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
        $user->setFirstName($attributes['name_first']);
        $user->setLastName($attributes['name_last']);
        $user->setVatsimStatusData($attributes);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
