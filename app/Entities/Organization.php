<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use App\Entities\Traits\SoftDeletes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="organizations")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Organization extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt, SoftDeletes;

    protected array $trackedProperties = [
        'name',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $prefix;

    /**
     * @ORM\OneToMany(targetEntity="DomainOrganization", mappedBy="organization")
     */
    protected Collection $organizationDomains;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationUser", mappedBy="organization", orphanRemoval=true)
     */
    protected Collection $organizationUsers;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationPrefixApplication", mappedBy="organization")
     */
    protected Collection $prefixApplications;

    /**
     * @ORM\OneToMany(targetEntity="Url", mappedBy="organization")
     */
    protected Collection $urls;

    public function __construct()
    {
        $this->organizationDomains = new ArrayCollection();
        $this->organizationUsers = new ArrayCollection();
        $this->prefixApplications = new ArrayCollection();
        $this->urls = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * @return Domain[]
     */
    public function getDomains(): array
    {
        return $this->organizationDomains
            ->map(fn ($organizationDomain) => $organizationDomain->getDomain())
            ->toArray();
    }

    /**
     * @param int|null $roleId
     * @return User[]
     */
    public function getUsers(int $roleId = null): array
    {
        if ($roleId === null) {
            return $this->organizationUsers
                ->map(fn (OrganizationUser $organizationUser) => $organizationUser->getUser())
                ->toArray();
        }

        return $this->organizationUsers
            ->filter(fn (OrganizationUser $organizationUser) => $organizationUser->getRoleId() === $roleId)
            ->map(fn (OrganizationUser $organizationUser) => $organizationUser->getUser())
            ->toArray();
    }

    public function getPrefixApplication(): ?OrganizationPrefixApplication
    {
        return $this->prefixApplications[0] ?? null;
    }

    /**
     * @return Url[]
     */
    public function getUrls(): array
    {
        return $this->urls->toArray();
    }
}
