<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="url_analytics")
 */
class UrlAnalytics extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint", options={"unsigned": true})
     */
    protected string $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected ?User $user = null;

    /**
     * @ORM\ManyToOne(targetEntity="Url", inversedBy="analytics")
     */
    protected ?Url $url = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $requestTime = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $httpHost = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $httpReferer = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $httpUserAgent = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $remoteAddr = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $requestUri = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var mixed
     */
    protected $getData = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @var mixed
     */
    protected $customHeaders = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $responseCode = null;

    public function getId(): string
    {
        return $this->id;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function setUrl(?Url $url): void
    {
        $this->url = $url;
    }

    public function setRequestTime(?string $requestTime): void
    {
        $this->requestTime = $requestTime;
    }

    public function setHttpHost(?string $httpHost): void
    {
        $this->httpHost = $httpHost;
    }

    public function setHttpReferer(?string $httpReferer): void
    {
        $this->httpReferer = $httpReferer;
    }

    public function setHttpUserAgent(?string $httpUserAgent): void
    {
        $this->httpUserAgent = $httpUserAgent;
    }

    public function setRemoteAddr(?string $remoteAddr): void
    {
        $this->remoteAddr = $remoteAddr;
    }

    public function setRequestUri(?string $requestUri): void
    {
        $this->requestUri = $requestUri;
    }

    public function setGetData($getData): void
    {
        $this->getData = $getData;
    }

    public function setCustomHeaders($customHeaders): void
    {
        $this->customHeaders = $customHeaders;
    }

    public function setResponseCode(?int $responseCode): void
    {
        $this->responseCode = $responseCode;
    }
}
