<?php

namespace App\Repositories;

use App\Entities\Url;
use App\Entities\User;
use App\Exceptions\NonUniqueUrlException;

class UrlRepository extends Repository
{
    public function findByUserOrTheirOrganizations(
        User $user,
        string $orderBy = 'id',
        string $order = 'ASC',
        int $perPage = null,
        int $page = null
    ) {
        if ($orderBy === 'fullUrl') {
            $extraSelect = ', CONCAT(d.url, CASE WHEN u.prefix = 1 then o.prefix else \'\' end, u.url) as HIDDEN fullUrl';
            $orderByQuery = "ORDER BY fullUrl $order";
        } elseif ($orderBy === 'redirectUrl') {
            $extraSelect = ', REPLACE(REPLACE(redirect_url, \'https://\', \'\'), \'http://\', \'\') as HIDDEN sortableRedirectUrl';
            $orderByQuery = "ORDER BY sortableRedirectUrl $order";
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

    public function findPublic(string $orderBy = 'id', string $order = 'ASC', int $perPage = null, int $page = null)
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
                u.global = 1
            )
            $orderByQuery
        DQL;

        $query = $this->getEntityManager()->createQuery($dql);

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
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
