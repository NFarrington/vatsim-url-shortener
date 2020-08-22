<?php

namespace App\Listeners;

use Doctrine\ORM\EntityManagerInterface;

class ResolveEntityManager
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function handle()
    {
        //
    }
}
