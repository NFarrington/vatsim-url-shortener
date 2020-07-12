<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="domain_organization")
 */
class DomainOrganization extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint", options={"unsigned": true})
     */
    protected string $id;

    /**
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="domainOrganizations")
     * @ORM\JoinColumn(nullable=false)
     */
    protected Domain $domain;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="organizationDomains")
     * @ORM\JoinColumn(nullable=false)
     */
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
