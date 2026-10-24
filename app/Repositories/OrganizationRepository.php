<?php

namespace App\Repositories;

use App\Entities\Organization;
use App\Entities\OrganizationUser;
use App\Entities\User;
use Illuminate\Support\Str;

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

    /**
     * Find all organizations, optionally only those whose name or prefix, or a member's name, contains the search
     * text, ignoring case. A numeric search also matches the organization's ID or a member's CID.
     */
    public function search(
        ?string $search,
        string $orderBy = 'id',
        string $order = 'ASC',
        ?int $perPage = null,
        ?int $page = null
    ) {
        $queryBuilder = $this->createQueryBuilder('o');

        if ($search !== null && $search !== '') {
            $memberConditions = ["LOWER(CONCAT(mu.firstName, ' ', mu.lastName)) LIKE :search ESCAPE '!'"];
            $conditions = [
                "LOWER(o.name) LIKE :search ESCAPE '!'",
                "LOWER(o.prefix) LIKE :search ESCAPE '!'",
            ];
            $queryBuilder->setParameter('search', '%'.addcslashes(Str::lower($search), '!%_').'%');

            if (ctype_digit($search)) {
                $conditions[] = 'o.id = :searchId';
                $memberConditions[] = 'mu.id = :searchId';
                $queryBuilder->setParameter('searchId', (int) $search);
            }

            $memberDql = $this->getEntityManager()->createQueryBuilder()
                ->select('ou.id')
                ->from(OrganizationUser::class, 'ou')
                ->join('ou.user', 'mu')
                ->where('ou.organization = o')
                ->andWhere('('.implode(' OR ', $memberConditions).')')
                ->getDQL();
            $conditions[] = "EXISTS ($memberDql)";

            $queryBuilder->where($queryBuilder->expr()->orX(...$conditions));
        }

        // Keep nulls last in both directions. SQLite and MariaDB put them first when ascending.
        if ($this->getClassMetadata()->isNullable($orderBy)) {
            $queryBuilder->addSelect("CASE WHEN o.$orderBy IS NULL THEN 1 ELSE 0 END AS HIDDEN orderByIsNull")
                ->orderBy('orderByIsNull', 'ASC')
                ->addOrderBy("o.$orderBy", $order);
        } else {
            $queryBuilder->orderBy("o.$orderBy", $order);
        }

        // Break ties on non-unique columns so rows don't shift between pages.
        if ($orderBy !== 'id') {
            $queryBuilder->addOrderBy('o.id', 'ASC');
        }

        $query = $queryBuilder->getQuery();

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
