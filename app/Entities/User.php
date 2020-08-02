<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use App\Entities\Traits\RoutesNotifications;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends Entity implements Authenticatable
{
    use RecordsCreatedAt, RecordsUpdatedAt, RoutesNotifications;

    protected array $trackedProperties = [
        'firstName',
        'lastName',
        'email',
        'emailVerified',
        'vatsimSsoData',
        'vatsimStatusData',
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\OneToMany(targetEntity="Revision", mappedBy="user")
     */
    protected Collection $revisions;

    /**
     * @ORM\OneToMany(targetEntity="Url", mappedBy="user")
     */
    protected Collection $urls;

    /**
     * @ORM\OneToMany(targetEntity="EmailVerification", mappedBy="user")
     */
    protected Collection $emailVerifications;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationPrefixApplication", mappedBy="user")
     */
    protected Collection $prefixApplications;

    /**
     * @ORM\OneToMany(targetEntity="OrganizationUser", mappedBy="user")
     */
    protected Collection $userOrganizations;

    /**
     * @ORM\Column(type="string")
     */
    protected string $firstName;

    /**
     * @ORM\Column(type="string")
     */
    protected string $lastName;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $email;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $emailVerified = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $admin = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $totpSecret = null;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected ?string $rememberToken = null;

    /**
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected ?string $vatsimConnectAccessToken = null;

    /**
     * @ORM\Column(type="text", length=65535, nullable=true)
     */
    protected ?string $vatsimConnectRefreshToken = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?DateTime $vatsimConnectTokenExpiry = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var mixed
     */
    protected $vatsimSsoData = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var mixed
     */
    protected $vatsimStatusData = null;

    public function __construct()
    {
        $this->revisions = new ArrayCollection();
        $this->urls = new ArrayCollection();
        $this->emailVerifications = new ArrayCollection();
        $this->prefixApplications = new ArrayCollection();
        $this->userOrganizations = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken(): string
    {
        return $this->rememberToken ?: '';
    }

    public function setRememberToken($token)
    {
        $this->rememberToken = $token;
    }

    public function getRememberTokenName(): string
    {
        return 'rememberToken';
    }

    public function getEmailVerification(): ?EmailVerification
    {
        return $this->emailVerifications[0] ?? null;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $verified)
    {
        $this->emailVerified = $verified;
    }

    public function getTotpSecret(): ?string
    {
        return $this->totpSecret;
    }

    public function setTotpSecret(?string $totpSecret): void
    {
        $this->totpSecret = $totpSecret;
    }

    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getDisplayInfo(): string
    {
        return "{$this->firstName} {$this->lastName} ({$this->id})";
    }

    public function setVatsimSsoData($vatsimSsoData): void
    {
        $this->vatsimSsoData = $vatsimSsoData;
    }

    public function setVatsimStatusData($vatsimStatusData): void
    {
        $this->vatsimStatusData = $vatsimStatusData;
    }

    public function isAdmin()
    {
        return $this->admin;
    }

    public function getVatsimConnectAccessToken(): ?string
    {
        return $this->vatsimConnectAccessToken;
    }

    public function setVatsimConnectAccessToken(?string $vatsimConnectAccessToken): void
    {
        $this->vatsimConnectAccessToken = $vatsimConnectAccessToken;
    }

    public function getVatsimConnectRefreshToken(): ?string
    {
        return $this->vatsimConnectRefreshToken;
    }

    public function setVatsimConnectRefreshToken(?string $vatsimConnectRefreshToken): void
    {
        $this->vatsimConnectRefreshToken = $vatsimConnectRefreshToken;
    }

    public function getVatsimConnectTokenExpiry(): ?Carbon
    {
        $expiry = $this->vatsimConnectTokenExpiry;

        return $expiry === null
            ? null
            : Carbon::instance($this->vatsimConnectTokenExpiry);
    }

    public function setVatsimConnectTokenExpiry(?DateTime $vatsimConnectTokenExpiry): void
    {
        $this->vatsimConnectTokenExpiry = $vatsimConnectTokenExpiry;
    }

    /**
     * @param int|null $roleId
     * @return Organization[]
     */
    public function getOrganizations(int $roleId = null): array
    {
        if ($roleId === null) {
            return $this->userOrganizations
                ->map(fn (OrganizationUser $organizationUser) => $organizationUser->getOrganization())
                ->toArray();
        }

        return $this->userOrganizations
            ->filter(fn (OrganizationUser $organizationUser) => $organizationUser->getRoleId() === $roleId)
            ->map(fn (OrganizationUser $organizationUser) => $organizationUser->getOrganization())
            ->toArray();
    }

    /**
     * @return OrganizationUser[]
     */
    public function getUserOrganizations(): array
    {
        return $this->userOrganizations->toArray();
    }

    public function setAdmin(bool $admin): void
    {
        $this->admin = $admin;
    }

    public function routeNotificationForMail(Notification $notification)
    {
        return $this->email;
    }
}
