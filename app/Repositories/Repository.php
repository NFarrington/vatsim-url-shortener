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
    public function findAll(string $orderBy = 'id', string $order = 'ASC', int $perPage = null, int $page = null)
    {
        $query = $this->createQueryBuilder('e')
            ->orderBy("e.$orderBy", $order)
            ->getQuery();

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
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
