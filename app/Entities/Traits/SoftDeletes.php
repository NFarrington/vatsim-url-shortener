<?php

declare(strict_types=1);

namespace App\Entities\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Support\Carbon;

trait SoftDeletes
{
    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected ?DateTime $deletedAt;

    public function getDeletedAt(): Carbon
    {
        return Carbon::instance($this->deletedAt);
    }

    public function setDeletedAt(?DateTime $deletedAt = null): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt && new DateTime('now') >= $this->deletedAt;
    }
}
