<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'domain_organization')]
#[ORM\Entity]
class DomainOrganization extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint', options: ['unsigned' => true])]
    protected string $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Domain::class, inversedBy: 'domainOrganizations')]
    protected Domain $domain;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'organizationDomains')]
    protected Organization $organization;

    public function getId(): string
    {
        return $this->id;
    }

    public function getDomain(): Domain
    {
        return $this->domain;
    }

    public function setDomain(Domain $domain): void
    {
        $this->domain = $domain;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization): void
    {
        $this->organization = $organization;
    }
}
