<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="domains")
 */
class Domain extends Entity
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    protected int $id;

    /**
     * @ORM\OneToMany(targetEntity="Url", mappedBy="domain")
     * @var Collection|Url[]
     */
    protected Collection $urls;

    /**
     * @ORM\OneToMany(targetEntity="DomainOrganization", mappedBy="domain")
     * @var Collection|DomainOrganization[]
     */
    protected Collection $domainOrganizations;

    /**
     * @ORM\Column(type="boolean", options={"default": 0})
     */
    protected bool $public = false;

    /**
     * @ORM\Column(type="string")
     */
    protected string $url;

    public function __construct()
    {
        $this->domainOrganizations = new ArrayCollection();
        $this->urls = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Url[]
     */
    public function getUrls(): array
    {
        return $this->urls->toArray();
    }

    public function addUrl(Url $url): void
    {
        $this->urls->add($url);
    }

    /**
     * @return DomainOrganization[]
     */
    public function getDomainOrganizations(): array
    {
        return $this->domainOrganizations;
    }

    /**
     * @return Organization[]
     */
    public function getOrganizations(): array
    {
        return $this->domainOrganizations
            ->map(fn (DomainOrganization $domainOrganization) => $domainOrganization->getOrganization())
            ->toArray();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = str_ends_with($url, '/')
            ? $url
            : "{$url}/";
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }
}
