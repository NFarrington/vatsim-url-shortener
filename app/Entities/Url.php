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
 * @ORM\Table(name="urls")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Url extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt, SoftDeletes;

    const URL_CACHE_KEY = self::class.'.domain-%s.prefix-%s.url-%s.';

    protected array $trackedProperties = [
        'user',
        'organization',
        'domain',
        'url',
        'redirectUrl',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="urls")
     */
    protected ?Organization $organization = null;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="urls")
     */
    protected ?User $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="urls", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    protected Domain $domain;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $prefix = false;

    /**
     * @ORM\Column(type="string")
     */
    protected string $url;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    protected string $redirectUrl;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $global = false;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $analyticsDisabled = false;

    /**
     * @ORM\OneToMany(targetEntity="UrlAnalytics", mappedBy="url")
     */
    protected Collection $analytics;

    public function __construct()
    {
        $this->analytics = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getDomain(): Domain
    {
        return $this->domain;
    }

    public function setDomain(Domain $domain): void
    {
        $this->domain = $domain;
    }

    public function isPrefixed(): bool
    {
        return $this->prefix;
    }

    public function setPrefix(bool $prefix): void
    {
        $this->prefix = $prefix;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function isAnalyticsDisabled(): bool
    {
        return $this->analyticsDisabled;
    }

    public function setAnalyticsDisabled(bool $analyticsDisabled): void
    {
        $this->analyticsDisabled = $analyticsDisabled;
    }

    public function getFullUrl(): string
    {
        return $this->domain->getUrl()
            .($this->prefix ? $this->organization->getPrefix().'/' : '')
            .$this->url;
    }
}
