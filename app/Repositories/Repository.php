<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

abstract class Repository extends EntityRepository
{
    public function findAllOrdered(string $orderBy = 'id', string $order = 'ASC', ?int $perPage = null, ?int $page = null)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->orderBy("e.$orderBy", $order);

        // Break ties on non-unique columns so rows don't shift between pages.
        if ($orderBy !== 'id') {
            $queryBuilder->addOrderBy('e.id', 'ASC');
        }

        $query = $queryBuilder->getQuery();

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }

    /**
     * @param iterable<object> $entities
     * @return array<int, int> Number of rows, keyed by the associated entity's ID.
     */
    protected function countByAssociation(string $association, iterable $entities): array
    {
        $ids = collect($entities)
            ->map(fn ($entity) => $entity->getId())
            ->all();

        if (empty($ids)) {
            return [];
        }

        $results = $this->createQueryBuilder('e')
            ->select("IDENTITY(e.$association) AS associationId", 'COUNT(e.id) AS associationCount')
            ->where("e.$association IN (:ids)")
            ->groupBy("e.$association")
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getArrayResult();

        return array_map('intval', array_column($results, 'associationCount', 'associationId'));
    }

    protected function paginateQuery(Query $query, int $perPage, int $page): LengthAwarePaginator
    {
        $query->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        $doctrinePaginator = new DoctrinePaginator($query);

        $path = Paginator::resolveCurrentPath();

        return new LengthAwarePaginator(
            new Collection($doctrinePaginator),
            $doctrinePaginator->count(),
            $perPage,
            $page,
            compact('path')
        );
    }
}
