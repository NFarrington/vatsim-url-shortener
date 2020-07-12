<?php

declare(strict_types=1);

namespace App\Entities;

use App\Entities\Traits\RecordsCreatedAt;
use App\Entities\Traits\RecordsUpdatedAt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="revisions")
 */
class Revision extends Entity
{
    use RecordsCreatedAt, RecordsUpdatedAt;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint", options={"unsigned": true})
     */
    protected string $id;

    /**
     * @ORM\Column(type="string")
     */
    protected string $modelType;

    /**
     * @ORM\Column(type="bigint", options={"unsigned": true})
     */
    protected string $modelId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="revisions")
     */
    protected ?User $user;

    /**
     * @ORM\Column(type="string")
     */
    protected string $propertyName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $oldValue;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $newValue;

    public function getId(): string
    {
        return $this->id;
    }

    public function setModelType(string $modelType): void
    {
        $this->modelType = $modelType;
    }

    public function setModelId(string $modelId): void
    {
        $this->modelId = $modelId;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function setPropertyName(string $propertyName): void
    {
        $this->propertyName = $propertyName;
    }

    public function setOldValue(?string $oldValue): void
    {
        $this->oldValue = $oldValue;
    }

    public function setNewValue(?string $newValue): void
    {
        $this->newValue = $newValue;
    }
}
