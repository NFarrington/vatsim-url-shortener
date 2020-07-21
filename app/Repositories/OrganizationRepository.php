<?php

namespace App\Repositories;

use App\Entities\User;

class OrganizationRepository extends Repository
{
    public function findByUser(
        User $user,
        string $orderBy = 'id',
        string $order = 'ASC',
        int $perPage = null,
        int $page = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('o, ou')
            ->join('o.organizationUsers', 'ou', 'WITH', "ou.user = {$user->getId()}")
            ->orderBy("o.$orderBy", $order)
            ->getQuery();

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }
}
