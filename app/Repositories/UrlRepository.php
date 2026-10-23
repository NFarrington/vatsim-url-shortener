<?php

namespace App\Repositories;

use App\Entities\Organization;
use App\Entities\Url;
use App\Entities\User;
use App\Exceptions\NonUniqueUrlException;

class UrlRepository extends Repository
{
    public function findByUserOrTheirOrganizations(
        User $user,
        string $orderBy = 'id',
        string $order = 'ASC',
        ?int $perPage = null,
        ?int $page = null
    ) {
        if ($orderBy === 'fullUrl') {
            $extraSelect = ', CONCAT(d.url, CASE WHEN u.prefix = 1 then o.prefix else \'\' end, u.url) as HIDDEN fullUrl';
            $orderByQuery = "ORDER BY fullUrl $order";
        } elseif ($orderBy === 'redirectUrl') {
            $extraSelect = ', REPLACE(REPLACE(u.redirectUrl, \'https://\', \'\'), \'http://\', \'\') as HIDDEN sortableRedirectUrl';
            $orderByQuery = "ORDER BY sortableRedirectUrl $order";
        } elseif ($orderBy === 'organization.name') {
            $extraSelect = '';
            $orderByQuery = "ORDER BY o.name $order";
        } else {
            $extraSelect = '';
            $orderByQuery = "ORDER BY u.$orderBy $order";
        }

        $dql = <<<DQL
            SELECT u, d $extraSelect FROM App\Entities\Url u
                JOIN u.domain d
                LEFT JOIN u.user user
                LEFT JOIN u.organization o
                LEFT JOIN o.organizationUsers ou
                LEFT JOIN ou.user orgUser
            WHERE user.id = :userId
                OR orgUser.id = :userId
            $orderByQuery
        DQL;

        $query = $this->getEntityManager()->createQuery($dql)->setParameters(['userId' => $user->getId()]);

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }

    public function findByOrganization(Organization $organization, ?int $perPage = null, ?int $page = null)
    {
        $query = $this->createQueryBuilder('u')
            ->select('u', 'd')
            ->join('u.domain', 'd')
            ->where('u.organization = :organization')
            ->orderBy('u.id', 'ASC')
            ->setParameter('organization', $organization)
            ->getQuery();

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }

    /**
     * @param iterable<Organization> $organizations
     * @return array<int, int> Number of URLs, keyed by organization ID.
     */
    public function countByOrganizations(iterable $organizations): array
    {
        return $this->countByAssociation('organization', $organizations);
    }

    public function findPublic(string $orderBy = 'id', string $order = 'ASC', ?int $perPage = null, ?int $page = null)
    {
        if ($orderBy === 'fullUrl') {
            $orderByQuery = "ORDER BY d.url $order, u.url $order";
        } else {
            $orderByQuery = "ORDER BY u.$orderBy $order";
        }

        $dql = <<<DQL
            SELECT u, d FROM App\Entities\Url u 
            JOIN u.domain d
            WHERE (
                u.user IS NULL
                AND u.organization IS NULL
            ) OR (
                u.public = 1
            )
            $orderByQuery
        DQL;

        $query = $this->getEntityManager()->createQuery($dql);

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }

    /**
     * Whether a URL exists with the same full URL, ignoring case. A prefixed URL matches
     * any organization with the prefix, since prefixes may not be unique.
     */
    public function existsIgnoringCase(string $domain, string $url, ?string $prefix): bool
    {
        $queryBuilder = $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->join('u.domain', 'd')
            ->leftJoin('u.organization', 'o')
            ->where('LOWER(d.url) = LOWER(:domain)')
            ->andWhere('LOWER(u.url) = LOWER(:url)')
            ->setParameter('domain', $domain)
            ->setParameter('url', $url);

        if ($prefix !== null) {
            $queryBuilder->andWhere('u.prefix = true')
                ->andWhere('LOWER(o.prefix) = LOWER(:prefix)')
                ->setParameter('prefix', $prefix);
        } else {
            $queryBuilder->andWhere('u.prefix = false');
        }

        return $queryBuilder->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Whether a non-prefixed URL on any domain equals the given path, ignoring case.
     */
    public function existsUnprefixedOnAnyDomainIgnoringCase(string $url): bool
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('LOWER(u.url) = LOWER(:url)')
            ->andWhere('u.prefix = false')
            ->setParameter('url', $url)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    public function findByDomainAndUrlAndPrefix(string $domain, string $url, ?string $prefix): ?Url
    {
        $dql = <<<DQL
            SELECT u, d FROM App\Entities\Url u 
                JOIN u.domain d 
                LEFT JOIN u.organization o 
            WHERE d.url = :domain
                AND u.url = :url
        DQL;
        $parameters = ['domain' => $domain, 'url' => $url];

        if ($prefix) {
            $dql .= ' AND u.prefix = true AND o.prefix = :prefix';
            $parameters['prefix'] = $prefix;
        } else {
            $dql .= ' AND u.prefix = false';
        }

        $urlEntities = $this->getEntityManager()->createQuery($dql)
            ->setParameters($parameters)
            ->getResult();

        if (count($urlEntities) > 1) {
            report(new NonUniqueUrlException());
        }

        return $urlEntities[0] ?? null;
    }
}
