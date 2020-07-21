<?php

declare(strict_types=1);

namespace App\Entities\Traits;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Illuminate\Support\Carbon;

trait RecordsUpdatedAt
{
    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     * @Gedmo\Timestampable(on="update")
     */
    protected DateTime $updatedAt;

    public function getUpdatedAt(): Carbon
    {
        return Carbon::instance($this->updatedAt);
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
