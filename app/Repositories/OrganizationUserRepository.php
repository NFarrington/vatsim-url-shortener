<?php

namespace App\Repositories;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;

class OrganizationUserRepository extends Repository
{
    public function findByUserAndOrganization(User $user, Organization $organization): ?OrganizationUser
    {
        return $this->findOneBy(['user' => $user, 'organization' => $organization]);
    }

    /**
     * @return OrganizationUser[]
     */
    public function findByOrganizationWithUsers(Organization $organization): array
    {
        return $this->createQueryBuilder('ou')
            ->select('ou', 'u')
            ->join('ou.user', 'u')
            ->where('ou.organization = :organization')
            ->orderBy('ou.roleId', 'ASC')
            ->addOrderBy('u.id', 'ASC')
            ->setParameter('organization', $organization)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param iterable<Organization> $organizations
     * @return array<int, int> Number of users, keyed by organization ID.
     */
    public function countByOrganizations(iterable $organizations): array
    {
        $organizationIds = collect($organizations)
            ->map(fn (Organization $organization) => $organization->getId())
            ->all();

        if (empty($organizationIds)) {
            return [];
        }

        $results = $this->createQueryBuilder('ou')
            ->select('IDENTITY(ou.organization) AS organizationId', 'COUNT(ou.id) AS userCount')
            ->where('ou.organization IN (:organizationIds)')
            ->groupBy('ou.organization')
            ->setParameter('organizationIds', $organizationIds)
            ->getQuery()
            ->getArrayResult();

        return array_map('intval', array_column($results, 'userCount', 'organizationId'));
    }
}
