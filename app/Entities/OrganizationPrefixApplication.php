<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use App\Entities\Traits\SoftDeletes;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="organization_prefix_applications")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class OrganizationPrefixApplication extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt, SoftDeletes;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="prefixApplications")
     * @ORM\JoinColumn(nullable=false)
     */
    protected Organization $organization;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="prefixApplications")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $user;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    protected string $identityUrl;

    /**
     * @ORM\Column(type="string")
     */
    protected string $prefix;

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getIdentityUrl(): string
    {
        return $this->identityUrl;
    }

    public function setIdentityUrl(string $identityUrl): void
    {
        $this->identityUrl = $identityUrl;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
}
