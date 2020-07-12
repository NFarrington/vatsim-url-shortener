<?php

namespace App\Repositories;

class NewsRepository extends Repository
{
    public function findPublished(string $orderBy = 'id', string $order = 'ASC', int $perPage = 20)
    {
        $query = $this->createQueryBuilder('n')
            ->where('n.published = true')
            ->orderBy("n.$orderBy", $order)
            ->getQuery();

        return $this->paginate($query, $perPage);
    }
}
