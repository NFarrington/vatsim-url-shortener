<?php

namespace App\Repositories;

class NewsRepository extends Repository
{
    public function findPublished(string $orderBy = 'id', string $order = 'ASC', int $perPage = null, int $page = null)
    {
        $query = $this->createQueryBuilder('n')
            ->where('n.published = true')
            ->orderBy("n.$orderBy", $order)
            ->getQuery();

        if ($perPage !== null) {
            return $this->paginateQuery($query, $perPage, $page);
        }

        return $query->execute();
    }
}
