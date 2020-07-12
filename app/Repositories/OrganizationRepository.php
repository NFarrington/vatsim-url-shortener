<?php

namespace App\Repositories;

use App\Entities\User;

class OrganizationRepository extends Repository
{
    public function findByUser(User $user, string $orderBy = 'id', string $order = 'ASC', int $perPage = 20)
    {
        $query = $this->createQueryBuilder('o')
            ->select('o, ou')
            ->join('o.organizationUsers', 'ou', 'WITH', "ou.user = {$user->getId()}")
            ->orderBy("o.$orderBy", $order)
            ->getQuery();

        return $this->paginate($query, $perPage);
    }
}
