<?php

namespace App\Repositories;

use Doctrine\ORM\EntityRepository;
use LaravelDoctrine\ORM\Pagination\PaginatesFromRequest;

abstract class Repository extends EntityRepository
{
    use PaginatesFromRequest;

    public function findAll(string $orderBy = 'id', string $order = 'ASC', int $perPage = 20)
    {
        $query = $this->createQueryBuilder('e')
            ->orderBy("e.$orderBy", $order)
            ->getQuery();

        return $this->paginate($query, $perPage);
    }
}
