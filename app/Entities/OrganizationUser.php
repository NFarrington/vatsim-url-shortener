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
 * @ORM\Table(name="organization_user")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class OrganizationUser extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt, SoftDeletes;

    const ROLE_OWNER = 1;
    const ROLE_MANAGER = 2;
    const ROLE_MEMBER = 3;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="organizationUsers")
     * @ORM\JoinColumn(nullable=false)
     */
    protected Organization $organization;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userOrganizations")
     * @ORM\JoinColumn(nullable=false)
     */
    protected User $user;

    /**
     * @ORM\Column(type="smallint")
     */
    protected int $roleId;

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

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function setRoleId(int $roleId): void
    {
        $this->roleId = $roleId;
    }
}
