<?php

namespace App\Repositories;

use App\Entities\Organization;
use App\Entities\User;

class OrganizationRepository extends Repository
{
    public function findByUser(
        User $user,
        string $orderBy = 'id',
        string $order = 'ASC',
        ?int $perPage = null,
        ?int $page = null
    ) {
        $query = $this->createQueryBuilder('o')
            ->select('o')
            ->join('o.organizationUsers', 'ou', 'WITH', "ou.user = {$user->getId()}")
            ->orderBy("o.$orderBy", $order)
            ->getQuery();

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }

    public function findOneByPrefixIgnoringCase(string $prefix): ?Organization
    {
        return $this->createQueryBuilder('o')
            ->where('LOWER(o.prefix) = LOWER(:prefix)')
            ->setParameter('prefix', $prefix)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param string[] $prefixes
     * @return array<string, Organization> Organizations with any of the prefixes, keyed by lowercased prefix.
     */
    public function findByPrefixesIgnoringCase(array $prefixes): array
    {
        $prefixes = array_values(array_unique(array_map('mb_strtolower', $prefixes)));
        if (empty($prefixes)) {
            return [];
        }

        $organizations = $this->createQueryBuilder('o')
            ->where('LOWER(o.prefix) IN (:prefixes)')
            ->setParameter('prefixes', $prefixes)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();

        $byPrefix = [];
        foreach ($organizations as $organization) {
            $byPrefix[mb_strtolower($organization->getPrefix())] ??= $organization;
        }

        return $byPrefix;
    }
}
