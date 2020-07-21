<?php

namespace App\Repositories;

use App\Entities\User;

class DomainRepository extends Repository
{
    public function findPublicOrOwnedByUser(
        User $user,
        string $orderBy = 'id',
        string $order = 'ASC',
        int $perPage = null,
        int $page = null
    ) {
        $dql = <<<DQL
            SELECT d FROM App\Entities\Domain d
            LEFT JOIN d.domainOrganizations do
            LEFT JOIN do.organization o
            LEFT JOIN o.organizationUsers ou
            LEFT JOIN ou.user u
            WHERE d.public = true
                OR u.id = :userId
            ORDER BY u.$orderBy $order
        DQL;
        $query = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameters(['userId' => $user->getId()]);

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }
}
